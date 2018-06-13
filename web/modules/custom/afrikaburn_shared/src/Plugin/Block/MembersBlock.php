<?php

namespace Drupal\afrikaburn_shared\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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

    $user = \Drupal::currentUser();
    $collective = \Drupal::routeMatch()->getParameter('node');

    return $collective
      ? [
        '#type' => 'view',
        '#name' => 'collective_members',
        '#display_id' => \Drupal::service('access_manager')->checkNamedRoute('afrikaburn_shared.admin', ['cid' => $collective->id()], $user) 
          ? 'admin_block'
          : 'members_block',
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
