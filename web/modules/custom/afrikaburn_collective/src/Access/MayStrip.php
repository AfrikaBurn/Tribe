<?php

/**
 * @file
 * Checks whether a user may strip admin privileges from admins in a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\user\Entity\User;
use Drupal\afrikaburn_collective\Controller\CollectiveController;

class MayStrip implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_may_strip';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $user) {

    list($collective, $member) = CollectiveController::pathParams();
    $user = \Drupal\user\Entity\User::load($account->id());
    $error = false;

    switch(true){
      case !CollectiveController::isAdmin($collective, $user):
        $error = '@user already not an admin!';
        break;
      case !CollectiveController::isAdmin($collective, $user):
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
