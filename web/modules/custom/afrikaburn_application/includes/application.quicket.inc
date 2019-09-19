<?php
/**
 * @file
 * Contains quicket bio utility functions
 */

use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;

/**
 * Add a subsidised ticket code
 * @param string $id_number
 * @param string $quicket_code
 */
function _subsidised($id_number, $quicket_code, $quicket_id){

  module_load_include('inc', 'afrikaburn_shared', 'includes/shared.quicket');

  $config = Drupal::config('afrikaburn_shared.settings');
  $event_id = $config->get('main_id');
  $existing = $quicket_code ? quicket_request(
    'GET',
    "codes/search?eventId=$event_id&text=$quicket_code"
  ) : FALSE;

  return $existing ? quicket_request(
    'PUT',
    "codes/$quicket_id",
    [
      'EventId' => $event_id,
      'IsPercentage' => FALSE,
      'DiscountAmount' => 0.0,
      'NumUses' => 6,
      'IsAccessCode' => TRUE,
      'Email' => str_replace(' ', '', $id_number),
      'TicketTypes' =>  array_values(
        array_unique(
          array_merge(
            array_filter(
              [
                $config->get('main_sub_id'),
              ]
            ),
            $existing ? $existing->TicketTypes : []
          )
        )
      ),
    ]
  ) : _generate($id_number);
}