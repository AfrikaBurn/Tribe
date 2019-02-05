<?php

/**
 * @file contains email notification utilities.
 */


/**
 * Send an email
 */
function _notify($to, $from_label, $from_address, $bcc, $subject, $message, $node){
  \Drupal::service('plugin.manager.mail')->mail(

    'afrikaburn_notification',
    'afrikaburn_notification',

    $to,
    \Drupal::currentUser()->getPreferredLangcode(),
    [
      'to' => $to,
      'from' => $from_label . '<' . $from_address . '>',
      'bcc' => $bcc,
      'subject' => $subject,
      'message' => $message,
      'node' => $node,
    ],
    $from_address,

    TRUE
  )['result']
    ? \Drupal::logger('mail-log')->notice('Mail sent to ' . implode(' & ', array_filter([$to, $bcc])) . "<br />" . $subject . "<hr />" . $message)
    : \Drupal::logger('mail-log')->notice('Mail send FAILED to ' . implode(' & ', [$to, $bcc]) . "<br />" . $subject . "<hr />" . $message);
}


/* ----- Recipients ----- */


/**
 * Retrieves title and a list of collective member email addresses.
 * @param  [object] $entity       Entity object being operated on
 * @return [array]                title and email addresses
 */
function _meta_collective($entity){

  module_load_include('inc', 'afrikaburn_alert', 'includes/alert');

  $members = [];
  $addresses = [];
  $collective = $entity->bundle() == 'collective'
    ? $entity
    : $entity->get('field_collective')
      ->first()
      ->get('entity')
      ->getTarget()
      ->getValue();

  if ($collective) {
    $member_uids = array_diff(
      array_column($collective->get('field_col_members')->getValue(), 'target_id'),
      _muted($collective)
    );
    $members = user_load_multiple($member_uids);

    foreach ($members as $member) {
      $email = $member->getEmail();
      $addresses[$email] = $email;
    }
    $title = $collective->get('title')->value;
  }

  return [
    'title' => $title,
    'members' => implode(',', $addresses),
  ];
}


/* ----- Message body ----- */


/**
 * Replace diff mail body.
 */
function _entity_diff($node, &$body){

  module_load_include('inc', 'afrikaburn_notification', 'includes/diff');
  $diff = _diff($node);
  $exclude = [
    'Invite Token',
    'Invite Mail',
    'Members',
    'Admins',
    'form_mode',
    'Supporting project',
    'Complete'
  ];

  if ($diff) {
    $rendered = '<table cellspacing=10 style="text-align: left;"><tr><th>Changed</th><th>From</th><th>To</th></tr>';
    foreach($diff as $title=>$change){
      if (!in_array($title, $exclude)){
        $rendered .= '<tr><td>' . implode('</td><td>', [$title, implode(',', $change[0]), implode(',', $change[1])]) .'</td></tr>';
      }
    }
    $rendered .= '</table>';
  }

  $body = str_replace('[diff]', $rendered, $body);
}

/**
 *  Replace entity rendering in mail body.
 */
function _entity_render($node, &$body){

  $renderings = [];

  preg_match_all('/\[render (?<mode>[^\[]+)\]/', $body, $renderings);
  foreach ($renderings['mode'] as $index=>$mode){

    if (!$rendered[$mode]){
      $rendered[$mode] = render(
        \Drupal::entityTypeManager()
          ->getViewBuilder('node')
          ->view($node, $mode)
      );
    }

    $body = str_replace($renderings[0][$index], $rendered[$mode], $body);
  }
}

/**
 *  Replace images in email
 */
function _email_images(&$message, &$body){

  $patterns = array(
    '/"(image:[^"]+)"/',
    '/src="(\/[^"]+\.gif[^"]*)"/',
    '/src="(\/[^"]+\.jpg[^"]*)"/',
    '/src="(\/[^"]+\.png[^"]*)"/',
    '/src="(\/[^"]+\.GIF[^"]*)"/',
    '/src="(\/[^"]+\.JPG[^"]*)"/',
    '/src="(\/[^"]+\.PNG[^"]*)"/',
  );

  $processed_images = array();
  foreach ($patterns as $pattern) {

    $embeddable_images = array();
    preg_match_all($pattern, $body, $embeddable_images);

    for ($i = 0; $i < count($embeddable_images[1]); $i++) {
      $raw = $embeddable_images[1][$i];
      $image_uri = trim(
        str_replace(
          ['/system/files', '/sites/default/files', '%20'],
          ['private:/', 'public:/', ' '],
          $raw
        )
      );

      if (!isset($processed_images[$image_uri])) {
        $image_path = preg_replace('/^(image:|\/)+/', '', $image_uri);
        $image_name = basename($image_path);

        $image = new stdClass();
        $image->uri = $image_path;
        $image->filename = $image_name;
        $image->filemime = \Drupal::service('file.mime_type.guesser')->guess($image_path);
        $image->cid = preg_replace('/[^0-9a-zA-Z]|\?.+$/', '', $raw);

        $message['params']['images'][] = $image;
        $body = str_replace($raw, 'cid:' . $image->cid, $body);
        $processed_images[$image_uri] = 1;
      }
    }
  }
}


/* ----- Entity Diff ----- */


/**
 * Compute an entity diff.
 */
function _diff($entity){

  static $diff;

  if (is_object($entity)){
    if (!$diff[$entity->id()]){
      $diff[$entity->id()] = $entity->original
        ? _entity_compare($entity, $entity->original)
        : FALSE;
    }
    return $diff[$entity->id()];
  }

  return $diff[$entity];
}

// Compute entity diff
function _entity_compare($entity_new, $entity_old) {

  $diff = FALSE;
  $bundle_fields = \Drupal::entityManager()->getFieldDefinitions('node', $entity_new->bundle());

  foreach($bundle_fields as $name=>$definition){

    if ($name == 'title' || preg_match('/^field_/', $name)){

      switch($definition->get('field_type')){

        case 'entity_reference':
        case 'image':
          $old = array_column($entity_old->get($name)->getValue(), 'target_id');
          $new = array_column($entity_new->get($name)->getValue(), 'target_id');
          if ($new != $old) {
            $diff[$definition->get('label')] = [count($old), count($new)];
          }
        break;

        default:
          $old = array_column($entity_old->get($name)->getValue(), 'value');
          $new = array_column($entity_new->get($name)->getValue(), 'value');

          if ($new != $old){
            $diff[$definition->get('label')] = [$old, $new];
          }

      }
    }
  }

  return $diff;
}
