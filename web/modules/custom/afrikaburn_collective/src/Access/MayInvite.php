<?php

/**
 * @file
 * Checks whether a user may be invited to a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\afrikaburn_collective\Controller\CollectiveController;

class MayInvite implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_may_invite';
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
      case CollectiveController::isInvited($cid, $uid):
        $error = '@user already invited!';
        break;
      case CollectiveController::isMember($cid, $uid):
        $error = '@user already a member!';
        break;
      case !(
          CollectiveController::setting($cid, 'open') ||
          CollectiveController::isMember($cid, $user) && CollectiveController::setting($cid, 'members_invite') ||
          CollectiveController::isAdmin($cid, $user)
        ):
        $error = 'You do not have permission to invite participants!';
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
