<?php

namespace Drupal\afrikaburn_bio\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Site\Settings;

/**
 * Provides a Ticket Block.
 *
 * @Block(
 *   id = "ticket_block",
 *   admin_label = @Translation("Ticket Block"),
 *   category = @Translation("Afrikaburn Blocks"),
 * )
 */
class TicketBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $uid = \Drupal::currentUser()->id();
    $settings = Settings::get('afrikaburn.quicket');
    $account = \Drupal\user\Entity\User::load($uid);
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('outdated');
    $outdated = $flag_service->getFlagging($flag, $account);

    if ($outdated){
      $links = ['<a class="button bio-update" href="/user/'.$uid.'/edit/update"><strong>Update your Bio</strong></a>'];
    }

    $links[] = '<a class="button bio-tickets" target="_blank" href="https://www.quicket.co.za/events/' . $settings['ticket_link'] . 'h=' . md5($uid) . '">Buy tickets</a><br/>';
#    $links[] = '<a class="button bio-tickets" href="/apply/anathi">Apply for Anathi tickets</a><br/>';
    $links[] = '<ul><li>&nbsp;&gt; <a target="_blank" href="https://www.afrikaburn.com/the-event/tickets">More about tickets</a></li></ul>';

    return [
      '#type' => 'markup',
      '#markup' => implode($links, '<br />'),
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }
}
