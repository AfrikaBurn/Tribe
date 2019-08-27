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
    $quicket_code = $account->get('field_quicket_code')
      ? $account->get('field_quicket_code')->getValue()[0]['value']
      : FALSE;

    if ($outdated || !$quicket_code){
      $links = ['
        <a class="button bio-update" href="/user/'.
        $uid.
        '/edit/update?get=tickets"><strong>Get tickets</strong></a>'
      ];
    } else if ($settings->get('tickets')['general']){

      if ($quicket_code){
        $links[] =
          '<a class="button bio-tickets" target="_blank" href="https://www.quicket.co.za/events/' .
          $settings->get('main_id') . '-?dc=' .
          $quicket_code .
          '">Get tickets</a>';
      } else {
        $links[] = 'Please update your Bio to be able to purchase tickets';
      }
    } else {
      $links[] = '<h2>General Ticket Sales closed</h2>';
    }
    $links[] = '<a class="more" target="_blank" href="https://www.afrikaburn.com/the-event/tickets">More about tickets</a>';

    return [
      '#type' => 'markup',
      '#markup' => implode($links, '<br />'),
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }
}
