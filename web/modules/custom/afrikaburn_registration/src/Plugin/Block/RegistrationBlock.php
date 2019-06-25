<?php

namespace Drupal\afrikaburn_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_collective\Utils;


/**
 * Provides a 'Registration' Block.
 *
 * @Block(
 *   id = "registration_block",
 *   admin_label = @Translation("Project registrations"),
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
    $show = [
      'current' =>
        CollectiveController::isAdmin($collective, $user) ||
        (CollectiveController::isMember($collective, $user) && CollectiveController::setting($collective, 'private_projects')) ||
        !CollectiveController::setting($collective, 'private_projects'),
      'past' =>
        CollectiveController::isAdmin($collective, $user) ||
        (CollectiveController::isMember($collective, $user) && CollectiveController::setting($collective, 'private_projects')) ||
        !CollectiveController::setting($collective, 'private_projects'),
      'new' =>
        CollectiveController::isAdmin($collective, $user) &&
        CollectiveController::setting($collective, 'projects'),
    ];

    foreach(array_filter($show) as $display=>$visible){

        switch (TRUE){

          case $display == 'new':
            $config = \Drupal::config('afrikaburn_registration.settings');

            foreach(_project_form_modes() as $key=>$map){
              $project = $map['title'];
              $modes = $map['modes'];
              if ($config->get($key . '/form_1')['open']){
                $links[] =
                  '<a href="/node/add/' . $key . '/form_1?field_collective=' . $cid .
                  '" target="_blank">' . $project . '</a>';
              }
            }

            $content = count($links)
              ? [
                '#type' => 'markup',
                '#markup' => '<ul class="new-project"><li>' . implode('</li><li>', $links) . '</li></ul>',
                '#cache' => $cache,
              ] : [
                '#cache' => $cache,
              ];
            break;

          case $display == 'current':
          case $display == 'past':

            $view = Views::getView('collective_projects');
            $view->execute($display);
            $count = count($view->result);

            $content = $count ? [
              '#type' => 'view',
              '#name' => 'collective_projects',
              '#display_id' => $display
            ] : FALSE;
        }

        if ($content){
          $group_name = 'group_' . $display;
          $tabs[$group_name] = [
            '#title' => [
              'new' => 'Register new',
              'current' => 'Current',
              'past' => 'Past',
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

      if ($tab_count == 1 && $tabs['group_new']) {
        $tabs['group_new']['content'] = [
          ['#markup' => '<em>Register a new:</em>'],
          [$tabs['group_new']['content']]
        ];
      }

      return $tab_count > 1
        ? [
            '#title' => $title,
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
  //   return
  //     CollectiveController::isMember($collective, $user) ||
  //     !CollectiveController::setting($collective, 'projects')
  //     ? [
  //         '#type' => 'view',
  //         '#name' => 'collective_projects',
  //         '#display_id' => 'current',
  //         '#cache' => [
  //           'max-age' => 0,
  //         ]
  //       ]
  //     : [
  //       '#cache' => [
  //         'max-age' => 0,
  //       ],
  //     ];
  }
}
