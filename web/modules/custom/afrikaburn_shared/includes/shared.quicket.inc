<?php
/**
 * @file
 * Contains quicket utility functions
 */

use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;

/**
 * Gets current events
 * @param array $waps array of users:
 *   [
 *    'FirstName': $first_name,
 *    'Surname': $last_name,
 *    'Email': $email,
 *   ]
 */
function quicket_request($method, $endpoint, $body = FALSE){

  $settings = Settings::get('afrikaburn.quicket');
  $result = FALSE;
  $options = [
    'headers' => [
      'Content-Type' => 'application/json',
      'usertoken' => $settings['user_token'],
    ],
    'timeout' => 45,
  ];

  if ($body) {
    $encoded = json_encode($body, JSON_PRETTY_PRINT);
    $options['body'] = $encoded;
  }

  try{

    \Drupal::logger('Quicket')->debug('Quicket call!<br /><pre>' . $e . '</pre>');
    \Drupal::logger('Quicket')->debug('Payload:<br /><pre>' . var_export($options, TRUE) . '</pre>');

    $client = \Drupal::httpClient();
    $api = (strpos($endpoint, '?') ? '&' : '?') . 'api_key=' . $settings['api_key'];
    $url = "https://api.quicket.co.za/api/$endpoint$api";
    $response = $client->request($method, $url, $options);

    if ($response && $response->getStatusCode() == 200){

      $response_string = $response->getBody()->getContents();
      $response_object = json_decode($response_string);

      return @array_shift(
        array_filter(
          [
            $response_object->result,
            $response_object->results,
            $response_string
          ]
        )
      );
    }

    return FALSE;

  } catch (Exception $e){

    \Drupal::logger('Quicket')->debug('Quicket call failure!<br /><pre>' . $e . '</pre>');
    \Drupal::logger('Quicket')->debug('Payload:<br /><pre>' . var_export($options, TRUE) . '</pre>');

    return FALSE;
  }
}

/**
 * Lists al quicket events for the configured quicket user.
 */
function quicket_get_events(){
  return quicket_request(
    'GET',
    'users/me/events'
  );
}
