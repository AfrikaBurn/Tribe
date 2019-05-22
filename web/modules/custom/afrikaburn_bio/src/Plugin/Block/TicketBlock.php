<?php

namespace Drupal\afrikaburn_bio\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use \Drupal\Core\Site\Settings;

/**
 * Provides a Ticket Block.
 * TODO: Move to shared
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
    $quicket = Settings::get('afrikaburn.quicket');
    $settings = \Drupal::config('afrikaburn_shared.settings');
    $account = \Drupal\user\Entity\User::load($uid);
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('outdated');
    $outdated = $flag_service->getFlagging($flag, $account);

    if ($outdated){
      $links = ['<a class="button bio-update" href="/user/'.$uid.'/edit/update"><strong>Update your Bio</strong></a>'];
    }

    if ($settings->get('tickets')['general']){
      $links[] = '<a class="button bio-tickets" target="_blank" href="https://www.quicket.co.za/events/' . $quicket['ticket_link'] . 'h=' . md5($uid) . '">Buy tickets</a><br/>';
    } else {
      $links[] = '<h2>General Ticket Sales closed</h2>';
    }
    if ($settings->get('tickets')['anathi']){
      $links[] = '<a class="button bio-tickets" href="/apply/anathi">Apply for Anathi tickets</a><br/>';
    }
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
