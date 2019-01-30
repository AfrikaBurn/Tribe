<?php
/**
 * @file
 * Contains quicket utility functions
 */

use \Drupal\Core\Site\Settings;
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

  $settings = Settings::get('afrikaburn.quicket');

  $ticket = [
    'TicketId' => $settings['wap_code'],
    'NumTickets' => 1
  ];

  foreach($waps as &$wap){
    $wap['OrderItems'] = $ticket;
  }

  $data = [
    'Guests' => $waps,
    'IsRsvp' => TRUE, // Is it?
    'SendMails' => TRUE,
  ];

  $options = [
    'headers' => [
      'Content-Type' => 'application/json',
      'usertoken' => $settings['user_token'],
    ],
    'timeout' => 45,
    'form_params' => $data,
  ];

  try{

    $client = \Drupal::httpClient();
    $response = $client->request(
      'POST',
      'https://api.quicket.co.za/api/events/' . $settings['event_id'] . '/orders/complimentaries?api_key=' . $settings['api_key'],
      $options
    );

    if ($response && $response->getStatusCode() == 200){
      return TRUE;
    }

    \Drupal::logger('WAPs')->debug('Quicket call failure!<br /><pre>' . var_export($result, TRUE) . '</pre>');
    return FALSE;

  } catch (Exception $e){
    \Drupal::logger('WAPs')->debug('Quicket call failure!<br /><pre>' . $e . '</pre>');
    return FALSE;
  }
}