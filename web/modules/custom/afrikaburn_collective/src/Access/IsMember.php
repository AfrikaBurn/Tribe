<?php

/**
 * @file
 * Checks whether a user is a member of a collective.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\afrikaburn_collective\Controller\CollectiveController;
use Drupal\afrikaburn_shared\Utils;

class IsMember implements AccessInterface {

  /**
   * Implements appliesTo().
   */
  public function appliesTo() {
    return '_is_member';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $account) {

    $user = Utils::currentUser($account);
    $candidate = Utils::getCandidate();
    $collective = Utils::currentCollective();

    switch(true){
      case !CollectiveController::isMember($collective, $user):
        $error = 'You are not a member of this collective!';
        break;
    }

    if ($error){
      Utils::showError($error, $user, $candidate);
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
