<?php

/**
 * @file
 * Checks whether a user may boot members from a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\afrikaburn_collective\Controller\CollectiveController;

class MayBoot implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_may_boot';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $user) {

    $uid = \Drupal::routeMatch()->getParameter('uid');
    $cid = \Drupal::routeMatch()->getParameter('cid');
    $error = false;

    switch(true){
      case CollectiveController::isMember($cid, $uid):
        $error = '@user already not a member!';
        break;
      case !CollectiveController::isAdmin($cid, $user):
        $error = 'You are not an administrator of this collective!';
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
