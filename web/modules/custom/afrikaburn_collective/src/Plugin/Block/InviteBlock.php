<?php

namespace Drupal\afrikaburn_collective\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_collective\Utils;


/**
 * Provides a group invitation block.
 *
 * @Block(
 *   id = "invite_block",
 *   admin_label = @Translation("Invite Block"),
 *   category = @Translation("Invite Block"),
 * )
 */
class InviteBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = Utils::currentUser();
    $collective = Utils::currentCollective();

    return
      CollectiveController::isAdmin($collective, $user) ||
      CollectiveController::setting($collective, 'open') && !CollectiveController::setting($collective, 'vetted')
      ? [
        '#type' => 'form',
        '#action' => '/collective/' . $collective->id() . '/invite',

        'emails' => [
          '#type' => 'textfield',
          '#required' => true,
          '#attributes' => [
            'size' => 34,
            'maxlength' => 2147483646,
            'placeholder' => 'john@smith.com, ncedi@shaya.com...',
            'name' => 'emails',
          ],
        ],

        'submit' => [
          '#type' => 'submit',
          '#value' => 'Send',
        ],

        '#cache' => [
          'max-age' => 0,
        ],
      ]
      : [
        '#cache' => [
          'max-age' => 0,
        ],
      ];
  }
}
