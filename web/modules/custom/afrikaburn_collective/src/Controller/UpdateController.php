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

  /* ----- Batch setup ----- */

  /**
   * Update Collectives
   */
  public static function update(){

    $batch = array(
      'title' => t('Updating collectives...'),
      'operations' => [],
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => '\Drupal\afrikaburn_collective\Controller\UpdateController::updateFinished',
    );

    $cids = \Drupal::entityQuery('node')
      ->condition('type', 'collective')
      ->execute();
    foreach ($cids as $cid) {
      $batch['operations'][] = [
        'Drupal\afrikaburn_collective\Controller\UpdateController::updateCollective',
        [$cid]
      ];
    }

    batch_set($batch);
  }

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


  /* ----- Batch operations ----- */


  /**
   * Update collective settings
   */
  public static function updateCollective($cid, &$context){

    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);
    $collective->set(
      'field_settings',
      [
        'projects',
        'private_projects',
        'private_discussion',
        'privileged_discussion',
        // 'emails'
      ]
    );
    $collective->save();

    $context['results'][] = 1;
  }

  /**
   * Resave user
   */
  public static function resaveUser($uid, &$context) {
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid)->save();
    $context['results'][] = 1;
  }


  /* ----- Batch complete ----- */


  /**
   * Collectives
   */
  public static function updateFinished($success, $results, $operations) {

    $abCollective = Node::create(
      array(
        'type' => 'collective',
        'title' => 'AfrikaBurn',
        'langcode' => 'en',
        'uid' => '1',
        'status' => 1,
        'field_settings' => ['public', 'open'],
        'field_col_admins' => [['target_id' => 1]],
      )
    );
    $uids = $query = \Drupal::entityQuery('user')->execute();
    $abCollective->set('field_col_members', $uids);
    $abCollective->save();

    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One Collective updated.', '@count Collectives updated.'
          )
        : t('Finished with errors.')
    );
  }

  /**
   * Users
   */
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