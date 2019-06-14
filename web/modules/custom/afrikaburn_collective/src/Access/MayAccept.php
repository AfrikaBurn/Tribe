<?php

/**
 * @file
 * Checks whether a user may accept an invitation to a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\afrikaburn_collective\Controller\CollectiveController;

class MayAccept implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_may_accept';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $account) {

    $uid = \Drupal::routeMatch()->getParameter('uid');
    $cid = \Drupal::routeMatch()->getParameter('cid');
    $error = false;

    switch(true){
      case CollectiveController::isBanned($cid, $uid):
        $error = '@user banned from this collective!';
        break;
      case !CollectiveController::isInvited($cid, $uid):
        $error = '@user not invited to join this collective!';
        break;
      case CollectiveController::isMember($cid, $uid):
        $error = '@user already a member!';
        break;
    }

    if ($error){
      drupal_set_message(
        t(
          $error,
          ['@user' => $user->get('id') == $account
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
