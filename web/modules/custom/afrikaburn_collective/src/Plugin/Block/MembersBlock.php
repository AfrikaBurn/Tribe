<?php

namespace Drupal\afrikaburn_collective\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_collective\Utils;


/**
 * Provides a group invitation block.
 *
 * @Block(
 *   id = "members_block",
 *   admin_label = @Translation("Members Block"),
 *   category = @Translation("Members Block"),
 * )
 */
class MembersBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = Utils::currentUser();
    $collective = Utils::currentCollective();

    if (
      CollectiveController::setting($collective, 'public_members') ||
      CollectiveController::setting($collective, 'private_members') && CollectiveController::isMember($collective, $user) ||
      CollectiveController::isAdmin($collective, $user)
    ) {
      $view = Views::getView('collective_members');
      $view->execute('requests');
      $requests = count($view->result);
      $title = $requests
        ? ['#markup' => 'Members <span class="alert">' . $requests . '</span>']
        : NULL;
    }

    return $collective
      ? [
        '#title' => $title,
        '#type' => 'view',
        '#name' => 'collective_members',
        '#display_id' => 'members_block',
        '#cache' => [
          'max-age' => 0,
        ],
      ] : [
        '#cache' => [
          'max-age' => 0,
        ],
      ];
  }
}
