<?php

namespace Drupal\afrikaburn_collective\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;

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

    module_load_include('inc', 'afrikaburn_collective', 'includes/util');

    $user = \Drupal::currentUser();
    $collective = \Drupal::routeMatch()->getParameter('node');
    $admin = afrikaburn_collective_admin();

    if ($admin) {
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
        '#display_id' => \Drupal::service('access_manager')->checkNamedRoute('afrikaburn_collective.admin', ['cid' => $collective->id()], $user)
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
