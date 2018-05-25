<?php

namespace Drupal\afrikaburn_shared\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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

    $user = \Drupal::currentUser();
    $collective = \Drupal::routeMatch()->getParameter('node');

    return $collective && \Drupal::service('access_manager')->checkNamedRoute('afrikaburn_shared.invite', ['cid' => $collective->id()], $user)
      ? [
        '#type' => 'form',
        '#action' => '/collective/' . $collective->id() . '/invite',
        
        'emails' => [
          '#type' => 'textfield',
          '#attributes' => [
            'size' => 34,
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

    return $form;
  }
}
