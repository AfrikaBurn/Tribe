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
use Drupal\afrikaburn_collective\Utils;

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
  public function access(AccountInterface $account) {

    $user = Utils::currentUser($account);
    $candidate = Utils::getCandidate();
    $collective = Utils::currentCollective();
    $error = false;

    switch(true){
      case $candidate && CollectiveController::isBanned($collective, $candidate):
        $error = '@user banned from this collective!';
        break;
      case $candidate && CollectiveController::isInvited($collective, $candidate):
        $error = '@user already invited!';
        break;
      case $candidate && CollectiveController::isMember($collective, $candidate):
        $error = '@user already a member!';
        break;
      case !(
          CollectiveController::setting($collective, 'open') ||
          CollectiveController::isMember($collective, $user) && CollectiveController::setting($collective, 'members_invite') ||
          CollectiveController::isAdmin($collective, $user)
        ):
        $error = 'You do not have permission to invite participants!';
        break;
    }

    if ($error){
      Utils::showError($error, $user, $candidate);
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
