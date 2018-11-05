<?php

/**
 * @file contains alert utilities.
 */


/**
 * Creates a system notification
 */
function _notification($type, $subject, $collective){

  $node = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->create(
    [
      'type' => 'notification',
      'field_notification_collective' => $collective,
      'field_notification_subject' => $subject,
      'field_notification_type' => $type,
    ]
  );
  $node->save();
  $users = [];

  switch $type {

    'post':
      $users = $collective
        ->get('field_col_members')
        ->referencedEntities();
      break;

    'comment':
      $users =
  }
}