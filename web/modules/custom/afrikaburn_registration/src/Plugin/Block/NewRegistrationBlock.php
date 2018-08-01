<?php

namespace Drupal\afrikaburn_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Project Registration' Block.
 *
 * @Block(
 *   id = "new_registration_block",
 *   admin_label = @Translation("Register a new project"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class NewRegistrationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $user = \Drupal::currentUser();
    $collective = \Drupal::routeMatch()->getParameter('node');
    $cid = $collective && $collective->bundle() == 'collective'
      ? $collective->id()
      : FALSE;

    if ($cid && \Drupal::service('access_manager')->checkNamedRoute('afrikaburn_collective.admin', ['cid' => $cid], $user)){

      module_load_include('inc', 'afrikaburn_registration', 'includes/form');

      $config = \Drupal::config('afrikaburn_registration.settings');
      $links = [];

      foreach(_project_form_modes() as $key=>$map){

        $project = $map['title'];
        $modes = $map['modes'];

        if ($config->get($key . '/form_1')['open']){
          $links[] =
            '<a href="/node/add/' . $key . '/form_1?field_collective=' . $cid .
            '" target="_blank">' . $project . '</a>';
        }
      }
    }

    return count($links)

      ? [
        '#type' => 'markup',
        '#markup' => '<ul><li>' . implode('</li><li>', $links) . '</li></ul>',
        '#cache' => [
          'max-age' => 0,
        ],
      ]

      : [
        '#cache' => [
          'max-age' => 0,
        ]
      ]
    ;
  }
}
