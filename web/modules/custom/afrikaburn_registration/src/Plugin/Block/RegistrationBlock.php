<?php

namespace Drupal\afrikaburn_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_collective\Utils;


/**
 * Provides a 'Registration' Block.
 *
 * @Block(
 *   id = "registration_block",
 *   admin_label = @Translation("Current registrations"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class RegistrationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = Utils::currentUser();
    $collective = Utils::currentCollective();

    return
      CollectiveController::isMember($collective, $user) ||
      !CollectiveController::setting($collective, 'private_projects')
      ? [
          '#type' => 'view',
          '#name' => 'collective_projects',
          '#display_id' => 'current',
          '#cache' => [
            'max-age' => 0,
          ]
        ]
      : [
        '#cache' => [
          'max-age' => 0,
        ],
      ];
  }
}
