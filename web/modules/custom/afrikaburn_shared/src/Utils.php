<?php

/**
 * @file
 * Collective utilities.
 */

namespace Drupal\afrikaburn_shared;


class Utils {


  /* --- User utilities --- */


  /**
   * Loads member from URL().
   */
  public static function getCandidate() {

    $uid = @array_shift(
      array_filter(
        [
          \Drupal::routeMatch()->getParameter('uid'),
          Utils::currentUser()->id(),
        ]
      )
    );

    return
      \Drupal\user\Entity\User::load(
        $uid
      );
  }

  /**
   * Loads current user.
   */
  public static function currentUser($account = false) {
    return
      $account
        ? \Drupal\user\Entity\User::load($account->id())
        : \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
  }


  /* --- Collective utilities --- */


  /**
   * Loads collective from URL().
   */
  public static function currentCollective() {

    $cid = @array_shift(
      array_filter(
        [
          \Drupal::routeMatch()->getParameter('cid'),
          \Drupal::routeMatch()->getParameter('nid'),
          \Drupal::routeMatch()->getParameter('node'),
        ]
      )
    );
    $node = @is_object($cid)
      ? $cid
      : @\Drupal::entityTypeManager()->getStorage('node')->load($cid);

    switch(TRUE){
      case $node && $node->bundle() == 'collective': return $node;
      case $node && $node->get('field_collective'): return $node->field_collective->entity;
      default: return FALSE;
    }
  }


  /* --- Entity Utilities --- */


  /**
   * Compute an entity diff.
   */
  public static function diff($entity){

    static $diff;

    if (is_object($entity)){
      if (!$diff[$entity->id()]){
        $diff[$entity->id()] = $entity->original
          ? Utils::entityCompare($entity, $entity->original)
          : FALSE;
      }
      return $diff[$entity->id()];
    }

    return $diff[$entity];
  }

  /**
   * Compares two entities
   */
  public static function entityCompare($entity_new, $entity_old) {

    $diff = FALSE;
    $bundle_fields = \Drupal::entityManager()->getFieldDefinitions('node', $entity_new->bundle());

    foreach($bundle_fields as $name=>$definition){

      if ($name == 'title' || preg_match('/^field_/', $name)){

        switch($definition->get('field_type')){

          case 'entity_reference':
          case 'image':
            $old = array_column($entity_old->get($name)->getValue(), 'target_id');
            $new = array_column($entity_new->get($name)->getValue(), 'target_id');
            if ($new != $old) {
              $diff[$definition->get('label')] = [count($old), count($new)];
            }
          break;

          default:
            $old = array_column($entity_old->get($name)->getValue(), 'value');
            $new = array_column($entity_new->get($name)->getValue(), 'value');

            if ($new != $old){
              $diff[$definition->get('label')] = [$old, $new];
            }

        }
      }
    }

    return $diff;
  }


  /* --- Message utilities --- */


  /**
   * Shows an error.
   */
  public static function showError($error, $user, $candidate) {

    if ($user->isAnonymous()) return;

    drupal_set_message(
      t(
        $error,
        ['@user' => $user->id() == $candidate->id()
          ? 'You are'
          : 'The participant is'
        ]
      ),
      'error'
    );
  }

  /**
   * Shows a status.
   */
  public static function showStatus($error, $user, $candidate) {
    drupal_set_message(
      t(
        $error,
        [
          '@user' => $user && $user->id() == $candidate->id()
            ? 'You are'
            : 'The participant is',
          '@username' => $user && $user->id() == $candidate->id()
            ? 'You are'
            : $candidate->get('name')->value . ' is'
        ]
      ),
      'status'
    );
  }
}
