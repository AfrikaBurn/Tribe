<?php

namespace Drupal\afrikaburn_registration\Plugin\Block;

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
    $flag = $flag_service->getFlagById('updated');
    $updated = $flag_service->getFlagging($flag, $account);
    $quicket_code = $account->get('field_quicket_code')->value;

    if ($updated){
      if (count($quicket_code)){
        $links[] = '<a class="button" target="_blank" href="https://www.quicket.co.za/events/'.$settings['event_id'].'-#/?dc='.$quicket_code.'">Buy tickets</a>';
      } else {
	$links = [
	  'Something went wrong! Please try:',
	  '<a href="/user/'.$uid.'/edit">Resaving your Bio</a>',
	  'Or, if that fails:',
	  '<a href="mailto:ict@afrikaburn.com">Contact tech support</a>'
	];
      }  
    } else {
      $links = [
        'To be eligible for ticket sales, please<br />',	      
        '<a class="button" href="/user/'.$uid.'/edit"><strong>Update your Bio</strong></a>',
      ];
    }

    return [
      '#type' => 'markup',
      '#markup' => '<h2>Tickets</h2>' . implode($links, '<br />'),
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }
}
