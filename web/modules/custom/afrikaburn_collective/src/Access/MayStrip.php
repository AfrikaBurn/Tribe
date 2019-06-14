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
use Drupal\afrikaburn_collective\Utils;

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
  public function access(AccountInterface $account) {

    $user = Utils::getUser($account);
    $candidate = Utils::getCandidate();
    $collective = Utils::currentCollective();
    $error = false;

    switch(true){
      case !CollectiveController::isAdmin($collective, $candidate):
        $error = '@user already not an admin!';
        break;
      case !CollectiveController::isAdmin($collective, $user):
        $error = 'You are not an administrator of this collective!';
        break;
      case $user->id() == $candidate->id():
        $error = 'You may not strip yourself!';
        break;
    }

    if ($error){
      Utils::showError($error, $user, $candidate);
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
