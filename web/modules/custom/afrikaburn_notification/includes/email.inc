<?php

/**
 * @file contains email notification utilities.
 */


/**
 * Send an email
 */
function _afrikaburn_notify($to, $from_label, $from_address, $bcc, $subject, $message, $node){
  \Drupal::service('plugin.manager.mail')->mail(

    'afrikaburn_notification',
    'project_registration',

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
    ? \Drupal::logger('mail-log')->notice('Mail sent to ' . implode(' & ', [$to, $bcc]) . "\n" . $subject . "\n:" . $message)
    : \Drupal::logger('mail-log')->notice('Mail send FAILED to ' . implode(' & ', [$to, $bcc]) . "\n" . $subject . "\n:" . $message);
}


/* ----- Recipients ----- */


/**
 * Retrieves title and a list of collective member email addresses.
 * @param  [object] $entity       Entity object being operated on
 * @return [array]            Array of email addresses
 */
function _afrikaburn_notification_collective($entity){

  $members = [];
  $addresses = [];

  $field_collective = $entity->get('field_collective');
  if ($field_collective) {
    $collective = $field_collective
      ->first()
      ->get('entity')
      ->getTarget();

    if ($collective) {
      $members = $collective
        ->get('field_col_members')
        ->referencedEntities();
      foreach ($members as $member) {
        $email = $member->getEmail();
        $addresses[$email] = $email;
      }
      $title = $collective->get('title')->value;
    }
  }

  return [
    'title' => $title,
    'members' => implode(',' ,$addresses),
  ];
}


/* ----- Message body ----- */


/**
 * Replace diff mail body.
 */
function _afrikaburn_notification_entity_diff($node, &$body){

  module_load_include('inc', 'afrikaburn_notification', 'includes/diff');
  $diff = _diff($node);
  if ($diff) {

    $rendered = '<table><tr><th>Changed</th><th>From</th><th>To</th></tr>';
    foreach($diff as $title=>$change){
      $rendered .= '<tr><td>' . implode('</td><td>', [$title, $change[0], $change[1]]) .'</td></tr>';
    }
    $rendered .= '</table>';
  } else {
    return FALSE;
  }

  $body = str_replace('[diff]', $rendered, $body);
}

/**
 *  Replace entity rendering in mail body.
 */
function _afrikaburn_notification_entity_render($node, &$body){

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
function _afrikaburn_notification_email_images(&$message, &$body){

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
      $image_uri = trim(str_replace('/system/files', 'private:/', $raw));

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