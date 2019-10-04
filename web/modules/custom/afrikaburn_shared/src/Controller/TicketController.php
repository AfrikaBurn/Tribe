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


  /* Ticket Classes */
  public static $GENERAL_TICKETS = 0;
  public static $DD_TICKETS = 1;
  public static $SUBSIDISED_TICKETS = 2;
  public static $ANATHI_TICKETS = 3;


  /* ----- Pages ----- */


  /**
   * Creates a listing of available tickets
   */
  public function ticketsPage(){

    $user = Utils::currentUser();
    $settings = \Drupal::config('afrikaburn_shared.settings');
    $codes = array_column($user->field_quicket_code->getValue(), 'value');
    $open = $settings->get('tickets');
    $markup = '';
    $codes = array_filter(
      [
        'anathi' => @$codes[self::$ANATHI_TICKETS],
        'subsidised' => @$codes[self::$SUBSIDISED_TICKETS],
        'ddt' => @$codes[self::$DD_TICKETS],
        'general' => @$codes[self::$GENERAL_TICKETS],
      ], function($quicket_code, $type) use ($open) {
        return $open[$type] && QuicketController::getTicketTypes(explode(' ', $quicket_code)[0]);
      },
      ARRAY_FILTER_USE_BOTH
    );

    switch (count($codes)) {
      case 0:
        $markup = \Drupal::config('afrikaburn_shared.closed_text')
          ->get('closed_text')['value'];
      break;
      case 1:
        return new TrustedRedirectResponse(
          'https://www.quicket.co.za/events/' . $settings->get('main_id') .
          '-?dc=' . array_shift($codes)
        );
      default:
        foreach($codes as $label=>$code) {
          $markup .=
            '<p class="ticket">' .
              '<a href="https://www.quicket.co.za/events/' .
                  $settings->get('main_id') . '-?dc=' . $code . '">' .
                'Get ' . $label . ' tickets' .
              '</a>' .
            '</p>';
        }
    }

    return ['#markup' => $markup];
  }


  /* ----- Ticket CRUD ----- */


  /**
   * Sets up default ticket types
   * @param $user to setup
   */
  public static function setup($user){

    $id_number = $user->field_id_number->value;
    $config = \Drupal::config('afrikaburn_shared.settings');
    $tickets = [];
    $template = [
      ['value' => self::$GENERAL_TICKETS],
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
      array_push($tickets, ...explode(' ', $config->get($key)));
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
    if ($user->field_quicket_code->getValue()[0]) {

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
   */
  public static function delete($user){
    foreach(@array_column($user->field_quicket_id->getValue(), 'value') as $quicket_id){
      QuicketController::delete($quicket_id);
    }
  }
}