<?php

namespace Drupal\afrikaburn_collective\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_collective\Utils;


/**
 * Provides a member listing block.
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
    $alert = '';
    $tabs = [];
    $show = [
      'members' =>
        CollectiveController::setting($collective, 'public_members') ||
        CollectiveController::setting($collective, 'private_members') && CollectiveController::isMember($collective, $user) ||
        CollectiveController::isAdmin($collective, $user),
      'invite' =>
        CollectiveController::isAdmin($collective, $user) ||
        CollectiveController::setting($collective, 'open') && !CollectiveController::setting($collective, 'vetted'),
      'requests' =>
        CollectiveController::isAdmin($collective, $user),
      'admins' =>
        CollectiveController::setting($collective, 'public_admins') ||
        CollectiveController::setting($collective, 'private_admins') && CollectiveController::isMember($collective, $user) ||
        CollectiveController::isAdmin($collective, $user),
      'banned' =>
        CollectiveController::isAdmin($collective, $user),
    ];

    foreach(array_filter($show) as $display=>$visible){

      $group_name = 'group_' . $display;
      $title = ucwords($display);

      switch (TRUE){

        case $display == 'invite':
          $content = [
            '#type' => 'form',
            '#action' => '/collective/' . $collective->id() . '/invite',

            'emails' => [
              '#type' => 'textarea',
              '#attributes' => [
                'size' => 34,
                'maxlength' => 2147483646,
                'placeholder' => 'john@smith.com, ncedi@shaya.com...',
                'name' => 'emails',
              ],
            ],

            'submit' => [
              '#type' => 'submit',
              '#value' => 'Send invites',
            ]
          ];
          break;

        case $display == 'requests':
        case $display == 'members':
        case $display == 'admins':
        case $display == 'banned':

          $view = Views::getView('collective_members');
          $view->execute($display);
          $count = count($view->result);

          $content = $count ? [
            '#type' => 'view',
            '#name' => 'collective_members',
            '#display_id' => $display
          ] : FALSE;

          $alert = $display == 'requests' && $count
            ? '<span class="alert">' . $count . '</span>'
            : '';

          break;
      }

      if ($content) {
        $tabs[$group_name] = [
          '#title' => [
            'invite' => 'Invite',
            'members' => 'All',
            'admins' => 'Admins',
            'banned' => 'Banned',
            'requests' => \Drupal::translation()->formatPlural($count, '1 Request', ':count Requests', [':count' => $count]),
          ][$display],
          '#type' => 'details',
          '#open' => TRUE,
          '#attributes' => ['id' => $group_name],
          'content' => $content,
        ];
      }
    }

    $tab_count = count($tabs);
    $cache = ['max-age' => 0];

    if ($tab_count) {

      return $tab_count > 1
        ? [
            '#title' => ['#markup' => 'Members' . $alert],
            'group_members' => array_merge(
              [
                '#type' => 'horizontal_tabs',
              ],
              $tabs
            ),
            '#cache' => $cache,
          ]
        : array_merge(array_shift($tabs)['content'], ['#cache' => $cache]);

    } else {
      return [
        '#cache' => $cache,
      ];
    }
  }
}
