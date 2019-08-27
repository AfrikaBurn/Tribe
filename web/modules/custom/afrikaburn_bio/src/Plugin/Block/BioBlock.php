<?php

namespace Drupal\afrikaburn_bio\Plugin\Block;

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
        'internal:/'.$uri,
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
    $flag = $flag_service->getFlagById('outdated');
    $outdated = $flag_service->getFlagging($flag, $account);
    $quicket_code = $account->get('field_quicket_code');

    $items = [
      $this::l('News feed', ''),
      $this::l('Activity', 'my/activity'),
      $this::l('Bookmarks', 'my/bookmarks'),
      $outdated
        ? $this::l('Update my details', 'user/'.$uid.'/edit/update')
        : $this::l('My Bio', 'user'),
      $this::l('My Privacy', 'my/privacy'),
      '<ul><li></li><li>'.$this::l('Delete my Bio', 'user/'.$uid.'/cancel').'</li></ul>',
      $this::l('Log out', 'user/logout'),
      \Drupal::service('masquerade')->isMasquerading()
      ? \Drupal::l(
        'Unmasquerade',
        \Drupal\Core\Url::fromRoute('masquerade.unmasquerade')
      ) : '',
    ];

    return [
      '#title' => ['#markup' => $account->get('name')->value],
      '#markup' => implode($items),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }
}
