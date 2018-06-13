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

  private function l($text, $uri){
    return \Drupal::service('link_generator')->generate(
      t($text), 
      \Drupal\Core\Url::fromUri(
        'internal://'.$uri, 
	['set_active_class' => TRUE]
      )
    );
  }

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

    $items = [
      $this::l('View my Bio', 'user'),
      $updated
        ? $this::l('Edit my Bio', 'user/'.$uid.'/edit')
        : $this::l('Update my Bio', 'user/'.$uid.'/edit/update'),
      $this::l('Log out', 'user/logout'),
      '<ul><li></li><li>'.$this::l('Delete my Bio', 'user/'.$uid.'/cancel').'</li></ul>'
    ];

    return [
      '#type' => 'markup',
      '#markup' => '<h2>My Bio</h2>' . implode($items, '<br />'),
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }
}
