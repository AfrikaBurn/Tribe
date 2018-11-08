<?php

/**
 * @file contains alert utilities.
 */


/**
 * Creates an alert
 */
function _alert($action, $collective, $subject){

  $user_func = '_' . $action . '_users';

  if (function_exists($user_func)){

    $users = $user_func($action, $collective, $subject);

    if (count($users)){

      $source = [
        'type' => 'alert',
        'field_alert_action' => [$action],
        'field_alert_collective' => [$collective],
      ];

      switch($action){
        case 'post':
          $source['field_alert_post'] = $subject;
        break;
        case 'comment':
          $source['field_alert_comment'] = $subject;
          $source['field_alert_post'] = $subject->getParentComment();
        break;
        default:
          $source['field_alert_project'] = $subject;
      }

      $alert = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->create($source);
      $alert->save();

      $flag = \Drupal::service('flag');
      $unread = $flag->getFlagById('unread');
      foreach($users as $user){
        $flag->flag($unread, $alert, $user);
      }
    }
  }
}


/* ----- Utility functions ----- */


/**
 * Get users that muted an entity.
 */
function _muted($entity){
  $flag = \Drupal::service('flag');
  $mute = $flag->getFlagById('mute');
  return $flag->getFlaggingUsers($entity, $mute);
}


/* ----- User functions ----- */


/**
 * Gets users to alert to a post.
 */
function _post_users($type, $collective, $subject){
  return array_diff(
    $collective
      ->get('field_col_members')
      ->referencedEntities(),
    _muted($collective)
  );
}

/**
 * Gets users to alert to a comment.
 */
function _comment_users($type, $collective, $subject){

  $comments = \Drupal::database()->select('comment_field_data', 'c');
  $comments->condition('c.pid', $subject->getParentComment()->cid->value);
  $comments->fields('c', ['uid']);
  $uids = array_unique($comments->execute()->fetchCol());
  $users = user_load_multiple($uids);

  return array_diff(
    $users,
    _muted($collective)
  );
}

/**
 * Gets users to alert to a create.
 */
function _create_users($type, $collective, $subject){
  return array_diff(
    $collective
      ->get('field_col_members')
      ->referencedEntities(),
    _muted($collective),
    _muted($subject)
  );
}

/**
 * Gets users to alert to an update.
 */
function _update_users($type, $collective, $subject){
  return array_diff(
    $collective
      ->get('field_col_members')
      ->referencedEntities(),
    _muted($collective),
    _muted($subject)
  );
}


/**
 * Gets users to alert to a delete.
 */
function _delete_users($type, $collective, $subject){
  return array_diff(
    $collective
      ->get('field_col_members')
      ->referencedEntities(),
    _muted($collective),
    _muted($subject)
  );
}
