<?php

namespace Drupal\afrikaburn_registration\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Bio Block.
 *
 * @Block(
 *   id = "bio_block",
 *   admin_label = @Translation("Bio Block"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class BioBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $uid = \Drupal::currentUser()->id();
    $account = \Drupal\user\Entity\User::load($uid);
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('updated');
    $updated = $flag_service->getFlagging($flag, $account);
    $quicket_code = $account->get('field_quicket_code');

    $links = [
      '<a href="/user">View my Bio</a>',
      $updated
        ? '<a href="/user/'.$uid.'/edit">Edit my Bio</a>'
        : '<a href="/user/'.$uid.'/edit/update"><strong>Update my Bio</strong></a>',
      '<a href="/user/logout">Log out</a>',
    ];

    return [
      '#type' => 'markup',
      '#markup' => '<h2>My Bio</h2>' . implode($links, '<br />'),
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }
}
