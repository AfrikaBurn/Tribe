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

    // Users to notify (minus current user)
    $users = user_load_multiple(
      array_diff(
        $user_func($action, $collective, $subject),
        [\Drupal::currentUser()->id()]
      )
    );

    $source = [
      'type' => 'alert',
      'field_alert_action' => [
        array_search(
          $action,
          [
            'post',
            'comment',
            'create',
            'update',
            'delete',
            // 'complete',
          ]
        )
      ],
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
      case 'update':
      case 'create':
      case 'delete':
        $source['field_alert_project'] = $subject;
    }

    $alert = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create($source);
    $alert->save();

    $flag = \Drupal::service('flag');
    $unread = $flag->getFlagById('unread');
    foreach($users as $user){
      if (!$flag->getFlagging($unread, $alert, $user)) {
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
  return array_map(
    '_uid',
    $flag->getFlaggingUsers($entity, $mute)
  );
}

/**
 * Reduce user to uid
 */
function _uid($user){
  return $user->uid->value;
}


/* ----- User functions ----- */


/**
 * Gets users to alert to a post.
 */
function _post_users($type, $collective, $subject){
  return array_diff(
    array_column($collective->field_col_members->getValue(), 'target_id'),
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
  $users = array_unique($comments->execute()->fetchCol());
  $users[] = array_column($subject->getParentComment()->uid->getValue(), 'target_id')[0];

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
    array_column($collective->field_col_members->getValue(), 'target_id'),
    _muted($collective)
  );
}

/**
 * Gets users to alert to an update.
 */
function _update_users($type, $collective, $subject){
  return array_diff(
    array_column($collective->field_col_members->getValue(), 'target_id'),
    _muted($collective),
    _muted($subject)
  );
}


/**
 * Gets users to alert to a delete.
 */
function _delete_users($type, $collective, $subject){
  return $collective->field_col_members
    ? array_diff(
        array_column($collective->field_col_members->getValue(), 'target_id'),
        _muted($collective),
        _muted($subject)
      )
    : [];
}