<?php

namespace Drupal\afrikaburn_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;


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

    module_load_include('inc', 'afrikaburn_collective', 'includes/util');

    $user = \Drupal::currentUser();
    $collective = \Drupal::routeMatch()->getParameter('node');

    return
      afrikaburn_collective_member() || !afrikaburn_collective_setting('private_projects')
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
