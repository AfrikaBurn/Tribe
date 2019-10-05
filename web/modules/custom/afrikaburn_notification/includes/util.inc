<?php

/**
 * @file contains email notification utilities.
 */

use \Drupal\afrikaburn_shared\Utils;
use \Drupal\afrikaburn_alert\Controller\AlertController;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;

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
    : $entity->field_collective->entity;

  if ($collective) {

    $member_uids = array_diff(
      array_map(
        function($user){ return $user->uid->value; },
        CollectiveController::getMembers($collective)
      ),
      AlertController::muted($collective)
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
    'collective' => $collective,
  ];
}


/* ----- Message body ----- */


/**
 * Replace diff mail body.
 */
function _entity_diff($node, &$body){

  module_load_include('inc', 'afrikaburn_notification', 'includes/diff');
  $diff = Utils::diff($node);
  $exclude = [
    'Invite Token',
    'Invite Mail',
    'Members',
    'Admins',
    'form_mode',
    'Supporting project',
    'Complete'
  ];

  $rendered = '';
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
