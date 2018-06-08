<?php

/**
 * @file
 * Contains quicket utility functions
 */

use \Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;

/**
 * Makes a request from quicket
 */
function _quicket($method, $id_number, $teens, $kids, $quicket_id = FALSE){

  $settings = Settings::get('afrikaburn.quicket');

  $ticket_types = array_filter(
    [
      $settings['ticket_codes']['general'],
      $kids ? $settings['ticket_codes']['kids'] : 0,
      $teens ? $settings['ticket_codes']['teens'] : 0
    ]
  );

  $data = json_encode(
    [
      'EventId' => $settings['event_id'],
      'TicketTypes' =>  $ticket_types,
      'IsPercentage' => FALSE,
      'DiscountAmount' => 0.0,
      'NumUses' => 2 + $kids + $teens,
      'ValidFrom' => $settings['valid_from'],
      'ValidTo' => $settings['valid_to'],
      'IsAccessCode' => TRUE,
      'Email' => str_replace(' ', '', $id_number),
    ]
  );

  $options = [
    'headers' => [
      'Content-Type' => 'application/json',
      'usertoken' => $settings['user_token'],
    ],
    'timeout' => 45,
    'body' => $data,
  ];

  try{
    $client = \Drupal::httpClient();
    $response = $client->request(
      $method,
      'https://api.quicket.co.za/api/codes' . ($quicket_id ? '/' . $quicket_id : '') . '?api_key=' . $settings['api_key'],
      $options
    );
    \Drupal::logger('afrikaburn_shared')->debug('<pre>' . var_export($response, TRUE) . '</pre>');

    if ($response && $response->getStatusCode() == 200){

      $result = json_decode($response->getBody()->getContents())->result;
      \Drupal::logger('afrikaburn_shared')->debug('<pre>' . var_export($result, TRUE) . '</pre>');

      return [
        'code' => $result->CodeValue,
        'id' => $result->CodeId,
      ];
    } else {
      \Drupal::logger('my_module')->error('An unexpected response has been received from quicket! ' . var_export($response, TRUE));
      return FALSE;
    }

  } catch (Exception $e){
    \Drupal::logger('my_module')->error('A request to quicket failed! ' . $e);
    return FALSE;
  }
}
