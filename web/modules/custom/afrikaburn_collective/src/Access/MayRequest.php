<?php

/**
 * @file
 * Checks whether a user may request to join a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\afrikaburn_collective\Controller\CollectiveController;

class MayRequest implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_may_request';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $user) {

    $uid = \Drupal::routeMatch()->getParameter('uid');
    $cid = \Drupal::routeMatch()->getParameter('cid');
    $error = false;

    switch(true){
      case CollectiveController::isBanned($cid, $uid):
        $error = '@user banned from this collective!';
        break;
      case !CollectiveController::setting($cid, 'open'):
        $error = 'This group is not open to participants joining!';
        break;
    }

    if ($error){
      drupal_set_message(
        t(
          $error,
          ['@user' => $user->get('id') == $uid
            ? 'You are'
            : 'The participant is'
          ]
        ),
        'error'
      );
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
