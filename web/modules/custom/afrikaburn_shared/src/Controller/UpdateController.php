<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_shared\UpdateController.
 */

namespace Drupal\afrikaburn_shared\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\node\Entity\Node;


class UpdateController extends ControllerBase {


  /* ----- Resave users ----- */


  /**
   * Resave all user records
   */
  public static function resaveUsers(){
    $uids = db_query('SELECT uid FROM {users} WHERE uid != 0')->fetchCol();
    $batch = [
      'title' => t('Resaving all users...'),
      'operations' => [],
      'progress_message' => t('Resaving @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_shared\Controller\UpdateController::usersResaved',
    ];
    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_shared\Controller\UpdateController::resaveUser',
        [$uid]
      ];
    }
    batch_set($batch);
  }

  public static function resaveUser($uid, &$context) {
    \Drupal::entityTypeManager()->getStorage('user')->load($uid)->save();
    $context['results'][] = 1;
  }

  public static function usersResaved($success, $results, $operations) {
    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One user resaved.', '@count users resaved.'
          )
        : t('Finished with errors.')
    );
  }


  /* ----- Quicket Data ----- */


  /**
   * Remove all quicket data
   */
  public static function wipeQuicket(){

    db_query("TRUNCATE {user__field_quicket_code}");
    db_query("TRUNCATE {user__field_quicket_id}");

    drupal_set_message('Quicket data wiped', 'status');

    return new RedirectResponse(\Drupal::url('afrikaburn_shared.settings'));
  }

  /**
   * Regenerate quicket data
   */
  public static function regenerateQuicketData($batch_size){

    $batch_size = $batch_size ? $batch_size : 500;

    $left = db_query("
      SELECT count({users}.uid)
      FROM {users}
      INNER JOIN {user__field_id_number}
      ON {user__field_id_number}.entity_id = {users}.uid
      LEFT JOIN {user__field_quicket_id}
      ON {user__field_quicket_id}.entity_id = {users}.uid
      LEFT JOIN {user__field_quicket_code}
      ON {user__field_quicket_code}.entity_id = {users}.uid
      WHERE
      (field_quicket_id_value IS NULL OR field_quicket_code_value IS NULL)
      AND {user__field_id_number}.field_id_number_value IS NOT NULL
      AND TRIM({user__field_id_number}.field_id_number_value) != ''
      AND {users}.uid > 0"
    )->fetchField();

    $uids = db_query("
      SELECT {users}.uid
      FROM {users}
      INNER JOIN {user__field_id_number}
      ON {user__field_id_number}.entity_id = {users}.uid
      LEFT JOIN {user__field_quicket_id}
      ON {user__field_quicket_id}.entity_id = {users}.uid
      LEFT JOIN {user__field_quicket_code}
      ON {user__field_quicket_code}.entity_id = {users}.uid
      WHERE
      (field_quicket_id_value IS NULL OR field_quicket_code_value IS NULL)
      AND {user__field_id_number}.field_id_number_value IS NOT NULL
      AND TRIM({user__field_id_number}.field_id_number_value) != ''
      AND {users}.uid > 0
      LIMIT $batch_size"
    )->fetchCol();

    $batch = [
      'title' => t('Generating quicket data for users...'),
      'operations' => [],
      'progress_message' => t(
        'Processing @current of @total users.<br />Batch size: %batch_size<br />Initial remaining: %left',
        ['%batch_size' => $batch_size, '%left' => $left]
      ),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_shared\Controller\UpdateController::quicketDataRegenerated',
    ];

    foreach ($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_shared\Controller\UpdateController::regenerateQuicketDatum',
        [$uid]
      ];
    }

    batch_set($batch);
  }

  public static function regenerateQuicketDatum($uid, &$context) {
    \Drupal::entityTypeManager()->getStorage('user')->load($uid)->save();
    $context['results'][] = 1;
  }

  public static function quicketDataRegenerated($success, $results, $operations){
    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One user processed.', '@count users processed.'
          )
        : t('Finished with errors.')
    );
  }


  /* ----- Add all users to the Afrikaburn collective ----- */


  /**
   * Add all members to the tribe collective
   */
  public static function addTribeMembers($batch_size){

    $batch_size = $batch_size ? $batch_size : 500;
    $cid = array_shift(\Drupal::entityQuery('node')
      ->condition('type', 'collective')
      ->condition('title', 'AfrikaBurn')
      ->execute());
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    $left = db_query("
      SELECT count({users}.uid)
      FROM {users}
      LEFT JOIN {flagging}
      ON {flagging}.uid = {users}.uid AND {flagging}.flag_id = 'member'
      WHERE
      {flagging}.flag_id IS NULL AND {users}.uid > 0"
    )->fetchField();

    $uids = db_query("
      SELECT {users}.uid
      FROM {users}
      LEFT JOIN {flagging}
      ON {flagging}.uid = {users}.uid AND {flagging}.flag_id = 'member'
      WHERE
      {flagging}.flag_id IS NULL AND {users}.uid > 0
      LIMIT $batch_size"
    )->fetchCol();

    $batch = [
      'title' => t('Adding users to AfrikaBurn collective...'),
      'operations' => [],
      'progress_message' => t(
        'Adding @current of @total users.<br />Batch size: %batch_size<br />Initial remaining: %left',
        ['%batch_size' => $batch_size, '%left' => $left]
      ),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_shared\Controller\UpdateController::tribeMembersAdded',
    ];

    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_shared\Controller\UpdateController::addTribeMember',
        [$collective, $uid]
      ];
    }

    batch_set($batch);
  }

  public static function addTribeMember($collective, $uid, &$context) {

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('member');
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);

    if (!$flag_service->getFlagging($flag, $collective, $user)){
      $flag_service->flag($flag, $collective, $user);
    }

    $context['results'][] = 1;
  }

  public static function tribeMembersAdded($success, $results, $operations) {
    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One user resaved.', '@count users added.'
          )
        : t('Finished with errors.')
    );
  }


  /* ----- Resave webform ----- */


  /**
   * Resave all webform results
   */
  public static function resaveWebformResults($webform_id, $batch_size){
    $sids = db_query('SELECT sid FROM {webform_submission} WHERE webform_id = ?', [$webform_id])->fetchCol();
    foreach($sids as $sid){
      \Drupal::entityTypeManager()->getStorage('webform_submission')->load($sid)->save();
    }
    drupal_set_message(count($sids) . ' results resaved!');
  }
}