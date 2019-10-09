<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_shared\TicketController.
 */

namespace Drupal\afrikaburn_shared\Controller;


use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\afrikaburn_shared\Controller\QuicketController;
use Symfony\Component\HttpFoundation\Response;
use \Drupal\Core\Routing\TrustedRedirectResponse;
use \Drupal\afrikaburn_shared\Utils;


class TicketController extends ControllerBase {

  /* Internal Ticket Lookups */
  private static $GENERAL_TICKETS = 0;
  private static $DD_TICKETS = 1;
  private static $SUBSIDISED_TICKETS = 2;
  private static $ANATHI_TICKETS = 3;
  private static $TRANSLATE = [
    'main_general_id' => 0,
    'main_ddt_id' => 1,
    'main_sub_id' => 2,
    'main_anathi_id' => 3,
  ];


  /* ----- Pages ----- */


  /**
   * Creates a listing of available tickets
   */
  public function ticketsPage(){

    $user = Utils::currentUser();
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById('outdated');

    $quicket_data = $user->get('field_quicket_code')->count();
    if ($flag_service->getFlagging($flag, $user) || !$quicket_data) {
      drupal_set_message('Please update your Bio to be able to purchase tickets');
      return new TrustedRedirectResponse(
        '/user/'. $user->id() . '/edit/update?get=tickets'
      );
    }

    $user_codes = array_column($user->get('field_quicket_code')->getValue(), 'value');
    if (!QuicketController::getTicketTypes($user_codes[0])){
      self::setup($user);
      $user->save();
      $user_codes = array_column($user->get('field_quicket_code')->getValue(), 'value');
    }

    $quicket = \Drupal::config('afrikaburn_shared.quicket');
    $tickets = \Drupal::config('afrikaburn_shared.tickets')->get('tickets');
    $open = [
      'general' => $tickets['general']['open'],
      'mayday' => $tickets['mayday']['open'],
      'ddt' => $tickets['ddt']['open'],
      'subsidised' => $tickets['subsidised']['open'],
      'anathi' => $tickets['anathi']['open'],
    ];

    $codes = array_filter(
      [
        'anathi' => @$user_codes[self::$ANATHI_TICKETS],
        'subsidised' => @$user_codes[self::$SUBSIDISED_TICKETS],
        'ddt' => @$user_codes[self::$DD_TICKETS],
        'general' => @$user_codes[self::$GENERAL_TICKETS],
        'mayday' => @$user_codes[self::$GENERAL_TICKETS],
      ],

      function($quicket_code, $type) use ($open) {
        return isset($quicket_code) && $quicket_code >= 0 && $open[$type];
      },

      ARRAY_FILTER_USE_BOTH
    );

    $code_count = count($codes);
    $markup = '';
    switch (TRUE) {
      case $code_count == 0:
        $markup = $tickets['closed']['value'];
      break;
      case $code_count == 1:
      case $code_count == 2 && $codes['general'] && $codes['mayday']:
        return new TrustedRedirectResponse(
          'https://www.quicket.co.za/events/' .
          $quicket->get('main_id') . '-?dc=' . array_shift($codes)
        );
      default:

        $markup = '<table>';
        $button = [
          'anathi' => 'Get my Anathi ticket',
          'subsidised' => 'Get my subsidised ticket',
          'ddt' => 'Get my DD ticket',
        ];

        foreach($codes as $key=>$code) {
          $markup .=
            '<tr><td colspan="2"><h2>' . strtoupper($key) . ' tickets</h2></td></th>' .
            '<tr>' .
              '<td>' . $tickets[$key]['description']['value'] . '</td>' .
              '<td><a class="button" target="_blank" href="https://www.quicket.co.za/events/' .
                $quicket->get('main_id') . '-?dc=' . $code . '">' .
                ($button[$key] ? $button[$key] : 'Get ' . $key . ' tickets') .
              '</a></td>' .
            '</tr>';
        }

        $markup .= '</table>';
    }

    $markup .= '<p><a href="https://www.afrikaburn.com/the-event/tickets">Find out more about tickets.</a></p>';

    return ['#markup' => $markup];
  }


  /* ----- Ticket CRUD ----- */


  /* -- BIO updates -- */

  /**
   * Sets up default ticket types
   * @param $user to setup
   */
  public static function setup($user){

    $id_number = $user->field_id_number->value;
    $quicket = \Drupal::config('afrikaburn_shared.quicket');
    $tickets = [];
    $template = [
      ['value' =>  self::$GENERAL_TICKETS],
      ['value' => -self::$DD_TICKETS],
      ['value' => -self::$SUBSIDISED_TICKETS],
      ['value' => -self::$ANATHI_TICKETS],
    ];

    foreach(
      [
        'main_general_id',
        'main_general_minor_id',
        'main_general_kids_id',
        'main_mayday_id',
        'main_mayday_minor_id',
        'main_mayday_kids_id',
      ] as $key
    ) {
      array_push($tickets, ...explode(' ', $quicket->get($key)));
    }

    $response = QuicketController::createTicketTypes(
      $id_number,
      $tickets,
      6
    );

    if ($response){

      $user->set(
        'field_quicket_code',
        array_replace(
          $template,
          $user->field_quicket_code->getValue(),
          [self::$GENERAL_TICKETS => ['value' => $response->CodeValue]]
        )
      );

      $user->set(
        'field_quicket_id',
        array_replace(
          $template,
          $user->field_quicket_id->getValue(),
          [self::$GENERAL_TICKETS => ['value' => $response->CodeId]]
        )
      );

    } else {

      $message = [
        '#markup' => '
          <p>There is a problem communicating with the ticket vendor to allow you to buy tickets.</p>
          <a href="mailto:support@afrikaburn.com">Please contact an administrator</a>.
        '
      ];

      drupal_set_message(
        render($message),
        'error'
      );
    }
  }

  /**
   * Updates a users ID
   * @param $user to update
   */
  public static function update($user){

    $general = @$user->field_quicket_code->getValue()[0];
    $valid = $general && QuicketController::getTicketTypes($general['value']);

    if ($general && $valid) {

      $quicket_codes = array_column($user->field_quicket_code->getValue(), 'value');
      $quicket_ids = array_column($user->field_quicket_id->getValue(), 'value');

      foreach($quicket_codes as $index=>$code){
        if ($code > -1){

          $existing = QuicketController::getTicketTypes($code);

          if ($existing) {
            QuicketController::updateId(
              $user->field_id_number->value,
              $code,
              $quicket_ids[$index],
              self::$GENERAL_TICKETS == $index ? 6 : 1
            );
          } else if (self::$GENERAL_TICKETS == $index) {
            self::setup($user);
          }
        }
      }
    } else {
      self::setup($user);
    }
  }

  /**
   * Removes all a users tickets from quicket
   * @param $user to delete
   */
  public static function delete($user){
    foreach(@array_column($user->field_quicket_id->getValue(), 'value') as $quicket_id){
      QuicketController::delete($quicket_id);
    }
  }


  /* -- Special tickets -- */


  /**
   * Add special tickets to a user
   * DDT, Subsidised or Anathi
   * @param $user to add to
   * @param $tickets array of string containing any of:
   *  main_ddt_id
   *  main_sub_id
   *  main_anathi_id
   */
  public static function addTickets($user, $tickets){

    $template = [
      ['value' =>  self::$GENERAL_TICKETS],
      ['value' => -self::$DD_TICKETS],
      ['value' => -self::$SUBSIDISED_TICKETS],
      ['value' => -self::$ANATHI_TICKETS],
    ];

    $id_number = $user->field_id_number->value;
    $quicket = \Drupal::config('afrikaburn_shared.quicket');
    $failed = FALSE;

    $quicket_codes = array_replace(
      $template,
      $user->field_quicket_code->getValue()
    );
    $quicket_ids = array_replace(
      $template,
      $user->field_quicket_id->getValue()
    );

    foreach($tickets as $ticket){

      $quicket_code = $quicket_codes[self::$TRANSLATE[$ticket]]['value'];
      $quicket_id = $quicket_ids[self::$TRANSLATE[$ticket]]['value'];

      if ($response = QuicketController::addTicketTypes(
        $id_number,
        $quicket_code,
        $quicket_id,
        explode(' ', $quicket->get($ticket)),
        1
      )) {
        $quicket_codes[self::$TRANSLATE[$ticket]]['value'] = $response->CodeValue;
        $quicket_ids[self::$TRANSLATE[$ticket]]['value'] = $response->CodeId;
      } else $failed = TRUE;
    }

    $user->set('field_quicket_code', $quicket_codes);
    $user->set('field_quicket_id', $quicket_ids);

    if ($failed) throw new \Exception('Ticket Exception: Could not add ticket types.');
  }
}