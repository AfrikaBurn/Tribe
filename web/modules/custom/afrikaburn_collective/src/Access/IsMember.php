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

    $user = \Drupal\user\Entity\User::load($account->id());

    return CollectiveController::isMember(
      CollectiveController::currentCollective(),
      $user
    )
      ? AccessResult::allowed()
      : AccessResult::forbidden();
  }
}
