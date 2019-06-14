<?php

/**
 * @file
 * Collective utilities.
 */

namespace Drupal\afrikaburn_collective;


class Utils {

  /**
   * Loads member from URL().
   */
  public static function getCandidate() {
    return
      \Drupal\user\Entity\User::load(
        \Drupal::routeMatch()->getParameter('uid')
      );
  }

  /**
   * Loads collective from URL().
   */
  public static function currentCollective() {

    $cid = @array_shift(
      array_filter(
        [
          \Drupal::routeMatch()->getParameter('cid'),
          \Drupal::routeMatch()->getParameter('node'),
        ]
      )
    );
    $node = is_object($cid)
      ? $cid
      : \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    return
      $node
      ? ($node->bundle() == 'collective'
        ? $node : ($node->get('field_collective')
            ? $node->get('field_collective')->value
            : false
          )
      ) : false;
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

  /**
   * Loads current user.
   */
  public static function showError($error, $user, $candidate) {
    drupal_set_message(
      t(
        $error,
        ['@user' => $user->get('id') == $candidate->get('id')
          ? 'You are'
          : 'The participant is'
        ]
      ),
      'error'
    );
  }
}
