<?php
/**
 * @file
 * Contains quicket registration utility functions
 */

use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;

/**
 * Sends WAP request to quicket
 * @param array $waps array of users:
 *   [
 *    'FirstName': $first_name,
 *    'Surname': $last_name,
 *    'Email': $email,
 *   ]
 */
function _send_WAPs($waps){

  module_load_include('inc', 'afrikaburn_shared', 'includes/shared.quicket');

  $config = Drupal::config('afrikaburn_shared.settings');

  $ticket = [
    'TicketId' => $config->get('wap_comp_id'),
    'NumTickets' => 1
  ];

  foreach($waps as &$wap){
    $wap['OrderItems'] = [$ticket];
  }

  return quicket_request(
    'POST',
    'events/' . $config->get('wap_id') . '/orders/complimentaries',
    [
      'Guests' => $waps,
      'IsRsvp' => FALSE,
      'SendMails' => TRUE,
    ]
  );
}