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
use Drupal\afrikaburn_collective\Utils;

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

    $user = Utils::getUser($account);
    $candidate = Utils::getCandidate();
    $collective = Utils::currentCollective();
    $error = false;

    switch(true){
      case $user->id() != $candidate->id():
        $error = 'How rude, this isn\'t you! Who the fuck are you?';
        break;
      case CollectiveController::isBanned($candidate, $candidate):
        $error = '@user banned from this collective!';
        break;
      case !CollectiveController::isInvited($candidate, $candidate):
        $error = '@user not invited to join this collective!';
        break;
      case CollectiveController::isMember($candidate, $candidate):
        $error = '@user already a member!';
        break;
    }

    if ($error){
      Utils::showError($error, $user, $candidate);
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }
}
