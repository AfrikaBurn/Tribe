<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_shared\QuicketController.
 */

namespace Drupal\afrikaburn_shared\Controller;


use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;


class QuicketController extends ControllerBase {


  /**
   * Performs a quicket request
   * @param $method   HTTP method to use
   * @param $endpoint HTTP endpiont to call
   * @param $body     Request body. Optional, defaults to false.
   * @return miced or FALSE for failure.
   */
  public static function request($method, $endpoint, $body = FALSE){

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

      $client = \Drupal::httpClient();
      $api = (strpos($endpoint, '?') ? '&' : '?') . 'api_key=' . $settings['api_key'];
      $url = "https://api.quicket.co.za/api/$endpoint$api";

      \Drupal::logger('Quicket')->debug(
        'Quicket call:<br />' .
        'URL: ' . $url . '<br />
        <pre>' . var_export($options, TRUE) . '</pre>'
      );

      try{
        $response = $client->request($method, $url, $options);
      } catch (\ClientException $e) {

        \Drupal::logger('Quicket')->debug('Quicket call failure!<br /><pre>' . $e . '</pre>');
        \Drupal::logger('Quicket')->debug('Payload:<br /><pre>' . var_export($options, TRUE) . '</pre>');

        return FALSE;
      }

      \Drupal::logger('Quicket')->debug(
        'Quicket response:<br />
        <pre>' . var_export($response, TRUE) . '</pre>'
      );

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

    } catch (\Exception $e){

      \Drupal::logger('Quicket')->debug('Quicket call failure!<br /><pre>' . $e . '</pre>');
      \Drupal::logger('Quicket')->debug('Payload:<br /><pre>' . var_export($options, TRUE) . '</pre>');

      return FALSE;
    }
  }


  /**
   * Lists al quicket events for the configured quicket user.
   * @return mixed array of events
   */
  public static function getEvents(){
    return self::request(
      'GET',
      'users/me/events'
    );
  }

  /**
   * Fetches exiting ticket types
   * @param $quicket_code of the user to return ticket types for.
   * @param $event_id     event to fetch ticket types for. Optional, defaults
   *                      to main event.
   */
  public static function getTicketTypes($quicket_code, $event_id = FALSE){

    $config = \Drupal::config('afrikaburn_shared.settings');
    $event_id = $event_id ? $event_id : $config->get('main_id');

    $existing = $quicket_code ? self::request(
      'GET',
      "codes/search?eventId=$event_id&text=$quicket_code"
    ) : FALSE;

    return $existing ? $existing->TicketTypes : [];
  }

  /**
   * Sets a users quicket ticket types
   * @param $id_number    id of the user to set ticket types for.
   * @param $ticket_types ticket types to set.
   * @param $num_uses     number of uses. Optional, defaults to 6.
   * @param $event_id     event to set ticket types for. Optional, defaults
   *                      to main event.
   */
  public static function createTicketTypes($id_number, $ticket_types, $num_uses = 6, $event_id = FALSE){

    $config = \Drupal::config('afrikaburn_shared.settings');
    $event_id = $event_id ? $event_id : $config->get('main_id');

    return self::request(
      'POST',
      'codes',
      [
        'EventId' => $config->get('main_id'),
        'IsPercentage' => FALSE,
        'DiscountAmount' => 0.0,
        'NumUses' => $num_uses,
        'IsAccessCode' => TRUE,
        'Email' => str_replace(' ', '', $id_number),
        'TicketTypes' => array_values(array_filter($ticket_types)),
      ]
    );
  }

  /**
   * Add ticket types to a user
   * @param $id_number    id of the user to add ticket types for.
   * @param $quicket_code quicket code of the user to add ticket types for.
   * @param $quicket_id   quicket id of the user to add ticket types for.
   * @param $ticket_types ticket types to add.
   * @param $num_uses     number of uses. Optional, defaults to 6.
   * @param $event_id     event to add ticket types for. Optional, defaults
   *                      to main event.
   */
  public static function addTicketTypes($id_number, $quicket_code, $quicket_id, $new_types, $num_uses = 6, $event_id = FALSE){

    $config = \Drupal::config('afrikaburn_shared.settings');
    $event_id = $event_id ? $event_id : $config->get('main_id');
    $existing_types = self::getTicketTypes($quicket_code, $event_id);

    if (count($existing_types) == 0) {
      if (count($new_types)) return self::createTicketTypes($id_number, $new_types, $num_uses, $event_id);
    } else {

      $ticket_types = array_unique(
        array_merge(
          array_values(array_filter($new_types)),
          array_values(array_filter($existing_types))
        )
      );

      return self::request(
        'PUT',
        "codes/$quicket_id",
        [
          'EventId' => $config->get('main_id'),
          'IsPercentage' => FALSE,
          'DiscountAmount' => 0.0,
          'NumUses' => $num_uses,
          'IsAccessCode' => TRUE,
          'Email' => str_replace(' ', '', $id_number),
          'TicketTypes' => $ticket_types,
        ]
      );
    }
  }

  /**
   * Updates a user ID
   * @param $id_number    id of the user to update.
   * @param $quicket_code quicket code of the user to update.
   * @param $quicket_id   quicket id of the user to update.
   * @param $num_uses     number of uses. Optional, defaults to 6.
   * @param $event_id     event to update. Optional, defaults
   *                      to main event.
   */
  public static function updateId($id_number, $quicket_code, $quicket_id, $num_uses = 6, $event_id = FALSE){
    self::addTicketTypes($id_number, $quicket_code, $quicket_id, [], $num_uses, $event_id);
  }

  /**
   * Sends Complimentary ticket request to quicket
   * @param array $comps array of users:
   *   [
   *    'FirstName' => $first_name,
   *    'Surname' => $last_name,
   *    'Email' => $email,
   *   ]
   * @param mixed $comp_id    id or array of ids of comp ticket(s) to send.
   * @param $num_uses         number of uses. Optional, defaults to 1.
   * @param array $event_id   id of event in which the comp lives.
   *                          Defaults to main event id.
   */
  public static function sendComps($comps, $comp_id, $num_uses = 1, $event_id = FALSE){

    $config = \Drupal::config('afrikaburn_shared.settings');
    $event_id = $event_id ? $event_id : $config->get('main_id');

    $comp_ids = is_array($comp_id) ? $comp_id : [$comp_id];
    $order_items = [];

    foreach($comp_ids as $id){
      $order_items[] = [
        'TicketId' => $id,
        'NumTickets' => $num_uses,
      ];
    }

    foreach($comps as &$comp){
      $comp['OrderItems'] = $order_items;
    }

    return self::request(
      'POST',
      'events/' . $event_id . '/orders/complimentaries',
      [
        'Guests' => $comps,
        'IsRsvp' => FALSE,
        'SendMails' => TRUE,
      ]
    );
  }

  /**
   * Deletes a users quicket details
   * @param $quicket_id   quicket id of the user to delete.
   */
  public static function delete($quicket_id){
    self::request(
      'DELETE',
      "codes/$quicket_id"
    );
  }
}