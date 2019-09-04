<?php

namespace Drupal\afrikaburn_shared\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;


/**
 * Provides a 'Registration' Block.
 *
 * @Block(
 *   id = "quickstart_block",
 *   admin_label = @Translation("Quickstart"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class Quickstart extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function access($account, $return_as_object = FALSE) {
    $config = \Drupal::config('afrikaburn_shared.quickstart');
    return
      $account->id() > 0 &&
      \Drupal::service('path.matcher')->isFrontPage() &&
      $config->get('quickstart')
        ? AccessResult::allowed()
        : AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = \Drupal::config('afrikaburn_shared.quickstart');

    return [
      '#type' => 'inline_template',
      '#template' => $config->get('quickstart')['value'],
    ];
  }
}