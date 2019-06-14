<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_collective\UpdateController.
 */

namespace Drupal\afrikaburn_collective\Controller;


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
      'finished' => '\Drupal\afrikaburn_collective\Controller\UpdateController::usersResaved',
    ];
    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_collective\Controller\UpdateController::resaveUser',
        [$uid]
      ];
    }
    batch_set($batch);
  }

  public static function resaveUser($uid, &$context) {
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid)->save();
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


  /* ----- Resave users ----- */


  /**
   * Resave all user records
   */
  public static function addTribeMembers(){

    $cid = array_shift(\Drupal::entityQuery('node')
      ->condition('type', 'collective')
      ->condition('title', 'AfrikaBurn')
      ->execute());
    $uids = db_query('SELECT uid FROM {users} WHERE uid != 0')->fetchCol();
    $batch = [
      'title' => t('Adding all users to AfrikaBurn...'),
      'operations' => [],
      'progress_message' => t('Resaving @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_collective\Controller\UpdateController::tribeMembersAdded',
    ];

    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_collective\Controller\UpdateController::addTribeMember',
        [$cid, $uid]
      ];
    }

    batch_set($batch);
  }

  public static function addTribeMember($cid, $uid, &$context) {

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('member');
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);
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

  /* ----- Migrate members ----- */


  public static function migrateCollectives(){
    $cids = db_query("SELECT nid FROM {node} WHERE {node}.type = 'collective'")->fetchCol();
    $batch = [
      'title' => t('Migrating collectives...'),
      'operations' => [],
      'progress_message' => t('Migrating @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_collective\Controller\UpdateController::collectivesMigrated',
    ];
    foreach($cids as $cid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_collective\Controller\UpdateController::migrateCollective',
        [$cid]
      ];
    }
    batch_set($batch);
  }

  public static function migrateCollective($cid, &$context) {

    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('member');
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);
    $members = [
      'members' => array_column($collective->get('field_col_members')->getValue(), 'target_id'),
      'admins' => array_column($collective->get('field_col_admins')->getValue(), 'target_id'),
    ];

    foreach($members as $role){
      foreach($role as $uid){
        $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
        if (!$flag_service->getFlagging($flag, $collective, $user)){
          $flag_service->flag($flag, $collective, $user);
        }
      }
    }

    $context['results'][] = 1;
  }

  public static function collectivesMigrated($success, $results, $operations) {
    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One collective migrated.', '@count collectives migrated.'
          )
        : t('Finished with errors.')
    );
  }
}