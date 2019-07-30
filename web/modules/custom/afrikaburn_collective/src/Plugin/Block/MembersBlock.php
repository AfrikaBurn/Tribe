<?php

namespace Drupal\afrikaburn_collective\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_collective\Utils;
use Drupal\Core\Access\AccessResult;


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
  public function access($account, $return_as_object = FALSE) {

    $user = Utils::currentUser();
    $collective = Utils::currentCollective();

    return (
      CollectiveController::isAdmin($collective, $user) ||
      CollectiveController::setting($collective, 'public_members') ||
      CollectiveController::setting($collective, 'public_admins') ||
      CollectiveController::isMember($collective, $user) && (
        CollectiveController::setting($collective, 'private_members') ||
        CollectiveController::setting($collective, 'private_admins')
      )
    )
    ? AccessResult::allowed()
    : AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = Utils::currentUser();
    $collective = Utils::currentCollective();
    $tabs = [
      'members' => FALSE,
      'invites' => FALSE,
      'requests' => FALSE,
      'admins' => FALSE,
      'banned' => FALSE,
    ];

    foreach($tabs as $display=>$ignored){
      $builder = 'build_' . $display . '_tab';
      $tabs[$display] = $this->$builder($user, $collective);
    }

    $tab_count = count($tabs);
    $cache = [
      // 'max-age' => 0,
      'tags' => $collective->getCacheTags()
    ];
    $alert = $tabs['requests'] && $tabs['requests']['content']['#count']
      ? '<span class="alert">' . $tabs['requests']['content']['#count'] . '</span>'
      : '';

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


  /* ---- Content builders ---- */


  /**
   * Builds a membership tab
   */
  private function build_members_tab($user, $collective){

    if (
      CollectiveController::isAdmin($collective, $user) ||
      CollectiveController::setting($collective, 'public_members') || (
        CollectiveController::setting($collective, 'private_members') &&
        CollectiveController::isMember($collective, $user)
      )
    ) {

      $content = $this->build_view('members');
      return $content
        ? $this->build_tab('All', 'members', $content)
        : FALSE;
    }
  }

  /**
   * Builds an invites tab
   */
  private function build_invites_tab($user, $collective){

    $existing = $this->build_view('invites');
    if ($existing) $existing['#prefix'] = '<br /><h3>Pending invitations</h3>';
    $content = array_filter(
      [
        CollectiveController::setting($collective, 'open') ||
        CollectiveController::isAdmin($collective, $user) || (
          CollectiveController::setting($collective, 'members_invite') &&
          CollectiveController::isMember($collective, $user)
        )
          ? $this->invitation_form($collective)
          : FALSE,
        CollectiveController::isAdmin($collective, $user)
          ? $existing
          : FALSE,
      ]
    );

    return count($content)
      ? $this->build_tab('Invites', 'members', $content)
      : FALSE;
  }


  /**
   * Builds a requests tab
   */
  private function build_requests_tab($user, $collective){

    if (CollectiveController::isAdmin($collective, $user)) {

      $content = $this->build_view('requests');

      return $content
        ? $this->build_tab(
            \Drupal::translation()->formatPlural(
              $content['#count'],
              '1 Request',
              ':count Requests',
              [':count' => $content['#count']]
            ),
            'requests',
            $content
          )
        : FALSE;
    }
  }

  /**
   * Builds an administrators tab
   */
  private function build_admins_tab($user, $collective){

    if (
      CollectiveController::isAdmin($collective, $user) ||
      CollectiveController::setting($collective, 'public_admins') || (
        CollectiveController::setting($collective, 'private_admins') &&
        CollectiveController::isMember($collective, $user)
      )
    ) {

      $content = $this->build_view('admins');
      return $content
        ? $this->build_tab('Admins', 'admins', $content)
        : FALSE;
    }
  }

  /**
   * Builds a banned tab
   */
  private function build_banned_tab($user, $collective){

    if (CollectiveController::isAdmin($collective, $user)){

      $content = $this->build_view('banned');
      return $content
        ? $this->build_tab('Banned', 'banned', $content)
        : FALSE;
    }
  }


  /* ---- Display building utilities ---- */


  /**
   * View display builder.
   */
  private function build_view($display){

    $title = ucwords($display);
    $view = Views::getView('collective_members');
    $view->execute($display);
    $count = count($view->result);

    return $count ? [
      '#type' => 'view',
      '#name' => 'collective_members',
      '#display_id' => $display,
      '#count' => $count,
      '#results' => $view->result,
    ] : FALSE;
  }

  /**
   * Tab builder
   */
  private function build_tab($title, $display, $content){

    $group_name = 'group_' . $display;

    return [
      '#title' => $title,
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => ['id' => $group_name],
      'content' => $content,
    ];
  }

  /**
   * Invitation form
   */
  private function invitation_form($collective){
    return [

      '#prefix' => '<h3>Invite people</h3>',

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
      ],
    ];
  }
}
