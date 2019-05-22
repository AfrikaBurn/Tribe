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


  /* ----- AB collective ----- */


  /**
   * Add users to AB collective
   */
  public static function update(){

    $batch = array(
      'title' => t('Populating AfrikaBurn...'),
      'operations' => [],
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_collective\Controller\UpdateController::updateFinished',
    );

    $cid = array_shift(\Drupal::entityQuery('node')
      ->condition('type', 'collective')
      ->condition('title', 'AfrikaBurn')
      ->execute());

    $uid_count = (int) db_query(
      "SELECT
          COUNT(uid)
        FROM
          d8_newusers LEFT JOIN d8_newnode__field_col_members
          ON
            uid = field_col_members_target_id
            AND entity_id = ?
        WHERE
          d8_newnode__field_col_members.field_col_members_target_id IS NULL
          AND uid > 0
        LIMIT 100
      ", [$cid]
    )->fetchCol('COUNT(uid)')[0];

    for($page = 0; $page <= $uid_count / 100; $page++) {

      $uids = db_query(
        "SELECT
            uid
          FROM
            d8_newusers LEFT JOIN d8_newnode__field_col_members
            ON
              uid = field_col_members_target_id
              AND entity_id = ?
          WHERE
            d8_newnode__field_col_members.field_col_members_target_id IS NULL
            AND uid > 0
          LIMIT 100
        ", [$cid]
      )->fetchCol('uid');

      $batch['operations'][] = [
        'Drupal\afrikaburn_collective\Controller\UpdateController::addUser',
        [$cid, $uids]
      ];
    }

    batch_set($batch);
  }
  public static function addUser($cid, $uid, &$context){
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    foreach($uids as $uid){
      $collective->field_col_members[] = [
        'target_id' => $uid
      ];
    }

    $collective->save();
    $context['results'][] = 1;
  }
  public static function updateFinished($success, $results, $operations) {
    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One user added.', '@count batch of users processed.'
          )
        : t('Finished with errors.')
    );
  }


  /* ----- Resave users ----- */


  /**
   * Resave all user records
   */
  public static function resave(){
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
}