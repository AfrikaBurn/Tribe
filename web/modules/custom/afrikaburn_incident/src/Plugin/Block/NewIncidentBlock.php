<?php

namespace Drupal\afrikaburn_incident\Plugin\Block;

use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Project Incident' Block.
 *
 * @Block(
 *   id = "new_incident_block",
 *   admin_label = @Translation("Log an Incident"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class NewIncidentBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    if(\Drupal::request()->get('types') == null) {
      return [
        '#prefix' => '<details>',
        'title' => [
          '#markup' => '<summary>Log an incident</summary>',
        ],
        'body' => [
          '#type' => 'view',
          '#name' => 'incident_type',
          '#display_id' => 'top',
          '#cache' => [
            'max-age' => 0,
          ],
        ],
        '#suffix' => '</details>',
      ];
    }

    $node = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->create(
        ['type' => 'incident']
      );
    $node->save();

    drupal_set_message($node->title->value . ' has been created. Please furnish the incident details:');

    return [
      'title' => [
        '#markup' => '<h2><span>Log an incident</span></h2>',
      ],
      'body' => [
        '#markup' => render(
          \Drupal::formBuilder()->getForm(
            \Drupal::entityTypeManager()
              ->getFormObject('node', 'default')
              ->setEntity($node)
          )
        ),
        '#cache' => [
          'max-age' => 0,
        ],
      ]
    ];
  }
}
