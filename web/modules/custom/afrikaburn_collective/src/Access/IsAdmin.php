<?php

/**
 * @file
 * Checks whether a user is an admin of a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\afrikaburn_collective\Controller\CollectiveController;
use Drupal\afrikaburn_shared\Utils;

class IsAdmin implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_is_admin';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $account) {

    $user = Utils::currentUser($account);
    $collective = Utils::currentCollective();

    switch(true){
      case $user->isAnonymous():
        $error = 'You need an account for this!';
        break;
      case !CollectiveController::isAdmin($collective, $user):
        $error = 'You are not an admin of this collective!';
        break;
    }

    if ($error){
      Utils::showError($error, $user, $candidate);
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
