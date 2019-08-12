<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_alert\AlertController.
 */

namespace Drupal\afrikaburn_alert\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_shared\Utils;


class AlertController extends ControllerBase {


  /* Posting */
  public static $ACTION_POST = 0;
  public static $ACTION_COMMENT = 1;

  /* Project registration CRUD */
  public static $ACTION_CREATE = 2;
  public static $ACTION_UPDATE = 3;
  public static $ACTION_DELETE = 4;
  public static $ACTION_COMPLETE = 5;

  /* Collective membership */
  public static $ACTION_REQUEST = 6;
  public static $ACTION_APPROVED = 7;
  public static $ACTION_INVITED = 8;
  public static $ACTION_BOOTED = 9;
  public static $ACTION_BANNED = 10;
  public static $ACTION_ADMINED = 11;

  /* Translation table */
  public static $ACTIONS = [
    'post',
    'comment',
    'create',
    'update',
    'delete',
    'complete',
    'request',
    'approved',
    'invited',
    'booted',
    'banned',
    'admined',
  ];


  /**
   * Create an alert.
   * @param $action     The action that triggered an alert.
   * @param $collective The collective in which the action occurred.
   * @param $action     The subject of the action.
   */
  public static function alert($action, $collective, $subject){

    $recipients = AlertController::recipients($action, $collective, $subject);
    $build = AlertController::build($action, $collective, $subject);

    $existing = AlertController::existing($build);
    if (count($existing)){
      \Drupal::service('database')->query(
        "DELETE FROM {flagging} WHERE entity_type = 'node' AND entity_id IN (:ids[])",
        [':ids[]' => $existing]
      );
    }

    if (count($recipients)){

      $alert = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->create($build);
      $alert->save();

      $flag = \Drupal::service('flag');
      $unread = $flag->getFlagById('unread');
      $users =  \Drupal\user\Entity\User::loadMultiple($recipients);

      foreach($users as $user){
        $flag->flag($unread, $alert, $user);
      }
    }
  }


  /* --- Recipients ---- */


  /**
   * Calculates who should be alerted.
   * @param $action     The action that triggered an alert.
   * @param $collective The collective in which the action occurred.
   * @param $subject    The subject of the action (optional)
   */
  private static function recipients($action, $collective, $subject){

    $user = Utils::currentUser();

    switch ($action) {

      case AlertController::$ACTION_POST:
      case AlertController::$ACTION_CREATE:
      case AlertController::$ACTION_UPDATE:
      case AlertController::$ACTION_DELETE:
      case AlertController::$ACTION_COMPLETE:

        return array_diff(
          array_map(
            function($user){ return $user->uid->value; },
            CollectiveController::getMembers($collective)
          ),
          AlertController::muted($collective)
        );

      case AlertController::$ACTION_COMMENT:

        return array_diff(
          array_merge(
            \Drupal::database()->select('comment_field_data', 'comment')
              ->condition('comment.pid', $subject->getParentComment()->cid->value)
              ->fields('comment', ['uid'])
              ->distinct()
              ->execute()
              ->fetchCol(),
            $subject->getParentComment()->uid->target_id
          ),
          AlertController::muted($collective)
        );

      case AlertController::$ACTION_REQUEST:

        return array_diff(
          array_map(
            function($user){ return $user->uid->value; },
            CollectiveController::getAdmins($collective)
          ),
          AlertController::muted($collective)
        );

      case AlertController::$ACTION_INVITED:
      case AlertController::$ACTION_APPROVED:

        return [$subject->uid->value];

      case AlertController::$ACTION_BOOTED:
      case AlertController::$ACTION_BANNED:
      case AlertController::$ACTION_ADMINED:

        return array_diff(
          array_merge(
            array_map(
              function($user){ return $user->uid->value; },
              CollectiveController::getAdmins($collective)
            ),
            [$subject->uid->value]
          ),
          AlertController::muted($collective)
        );

      default: return [];
    }
  }


  /* --- Utilities ---- */


  /**
   * Create an alert build array.
   * @return array build array
   */
  private static function build($action, $collective, $subject){

    $build = [
      'type' => 'alert',
      'field_alert_action' => $action,
      'field_alert_collective' => [$collective],
    ];

    switch($action){

      case AlertController::$ACTION_POST:
        $build['field_alert_post'] = $subject;
      break;

      case AlertController::$ACTION_COMMENT:
        $build['field_alert_comment'] = $subject;
        $build['field_alert_post'] = $subject->getParentComment();
      break;

      case AlertController::$ACTION_CREATE:
      case AlertController::$ACTION_UPDATE:
      case AlertController::$ACTION_DELETE:
        $build['field_alert_project'] = $subject;
        $build['field_alert_project_mode'] = $subject->field_form_mode->value;
      break;

      case AlertController::$ACTION_INVITED:
      case AlertController::$ACTION_APPROVED:
      case AlertController::$ACTION_BOOTED:
      case AlertController::$ACTION_BANNED:
        $build['field_alert_user'] = $subject;
      break;
    }

    return $build;
  }

  /**
   * Finds existing alerts
   * @param $build build array to search for.
   * @return array of existing alert ids.
   */
  private static function existing($build){

    $query = \Drupal::entityQuery('node')->condition('type', 'alert');

    foreach($build as $field => $value){

      $safe = is_array($value) ? $value[0] : $value;
      $value = is_object($safe) ? $safe->id() : $safe;

      $query->condition(
        $field,
        $value
      );
    }
    return $query->execute();
  }

  /**
   * Retrieves uids of users that have muted a node.
   * @param $node Node to check mutes against.
   */
  public static function muted($node){

    $flag = \Drupal::service('flag');
    $mute = $flag->getFlagById('mute');

    return array_map(
      function($user){ return $user->uid->value; },
      $flag->getFlaggingUsers($node, $mute)
    );
  }

}