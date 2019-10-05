<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_shared\Forms\TicketSettings.
 */

namespace Drupal\afrikaburn_shared\Forms;


use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\afrikaburn_shared\Controller\QuicketController;
use \Drupal\afrikaburn_shared\Controller\UpdateController;


/**
 * Defines a form that configures forms module settings.
 */
class TicketSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_ticket_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'afrikaburn_shared.quicket',
      'afrikaburn_shared.tickets',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    $quicket = $this->config('afrikaburn_shared.quicket');
    $tickets = $this->config('afrikaburn_shared.tickets')->get('tickets');

    $form['tabs'] = [
      '#type' => 'horizontal_tabs',
      '#entity_type' => 'config',
      '#group_name' => 'settings_tabs',
      '#bundle' => 'none',

      'tickets' => [
        '#title' => 'Ticket Sales',
        '#type' => 'details',
        '#open' => TRUE,
        '#tree' => TRUE,
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

        'closed' => [
          '#title' => 'Text to display when all sales are closed',
          '#type' => 'text_format',
          '#default_value' => $tickets['closed']['value'],
          '#format' => $tickets['closed']['format'],
        ],
      ],

      'quicket' => [
        '#title' => 'Quicket Integration',
        '#type' => 'details',
        '#open' => TRUE,
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

        'defined' => [
          '#type' => 'fieldset',
          '#title' => 'Defined events',
        ],

        'ids' => [
          '#type' => 'fieldset',
          '#tree' => FALSE,
          '#title' => 'Main event',
        ],
      ],
    ];

    $this->buildEvents($form);
    $this->buildTickets($form, $quicket);
    $this->buildSales($form, $tickets);

    return parent::buildForm($form, $form_state);
  }

  /**
   * Builds defined events section
   * @param $form
   */
  function buildEvents(&$form){
    $events = QuicketController::getEvents();

    foreach($events as $id=>$event){
      $form['tabs']['quicket']['defined'][$id] = [

        '#type' => 'details',
        '#open' => FALSE,
        '#title' => $event->name,
        'description' => [
          '#markup' => $event->description,
          '#weight' => 1,
          '#prefix' => '<br />'
        ],

        'id' => [
          '#title' => 'Event ID',
          '#type' => 'textfield',
          '#value' => $event->id,
          '#attributes' => ['disabled' => 'disabled'],
        ],
      ];

      foreach($event->tickets as $ticket){
        $form['tabs']['quicket']['defined'][$id][] = [
          '#type' => 'textfield',
          '#title' => $ticket->name,
          '#value' => $ticket->id,
          '#attributes' => ['disabled' => 'disabled']
        ];
      }
    }
  }

  /**
   * Builds Quicket integration section
   * @param $form
   * @param $quicket
   */
  function buildTickets(&$form, $quicket){
    foreach (
      [
        'main_id' => ['Event ID', 1],
        'main_general_id' => ['General'],
        'main_general_minor_id' => ['General Minor'],
        'main_general_kids_id' => ['General Kids', 1],
        'main_mayday_id' => ['Mayday'],
        'main_mayday_minor_id' => ['Mayday Minor'],
        'main_mayday_kids_id' => ['Mayday Kids', 1],
        'main_ddt_id' => ['Direct Distribution'],
        'main_sub_id' => ['Subsidised'],
        'main_anathi_id' => ['Anathi'],
      ] as $key => $def
    ) {
      $form['tabs']['quicket']['ids'][$key] = [
        '#type' => 'textfield',
        '#title' => $def[0],
        '#default_value' => $quicket->get($key),
        '#placeholder' => 'Ticket ID',
      ];
      if (@$def[1]) $form['tabs']['quicket']['ids'][$key]['#suffix'] = '<br />';
    }

    foreach(
      [
        'wap', 'vp'
      ] as $comp
    ) {
      $form['tabs']['quicket'][] = [
        '#type' => 'fieldset',
        '#tree' => FALSE,
        '#title' => strtoupper($comp) . ' Event',
        ($comp . '_id') => [
          '#type' => 'textfield',
          '#title' => 'Event ID',
          '#default_value' => $quicket->get($comp . '_id'),
          '#attributes' => ['placeholder' => 'Using main Event ID'],
        ],
        ($comp . '_comp_id') => [
          '#type' => 'textfield',
          '#title' => strtoupper($comp),
          '#default_value' => $quicket->get('wap_comp_id'),
          '#placeholder' => 'Ticket ID',
        ],
      ];
    }
  }

  /**
   * Builds Ticket Sales section
   * @param $form
   * @param $quicket
   */
  function buildSales(&$form, $tickets){
    foreach(
      [
        'general' => 'General',
        'mayday' => 'Mayday',
        'ddt' => 'Direct Distribution',
        'subsidised' => 'Subsidised',
        'anathi' => 'Anathi',
      ] as $key => $label
    ) {
      $form['tabs']['tickets'][$key] = [
        '#type' => 'details',
        '#title' => $label,
        '#tree' => TRUE,

        'open' => [
          '#type' => 'checkbox',
          '#title' => 'Open',
          '#default_value' => $tickets[$key]['open'],
        ],

        'description' => [
          '#title' => 'Description',
          '#type' => 'text_format',
          '#default_value' => $tickets[$key]['description']['value'],
          '#format' => $tickets[$key]['description']['format'],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    $this->configFactory->getEditable('afrikaburn_shared.quicket')
      ->set('main_id', $values['main_id'])
      ->set('main_general_id', $values['main_general_id'])
      ->set('main_general_minor_id', $values['main_general_minor_id'])
      ->set('main_general_kids_id', $values['main_general_kids_id'])
      ->set('main_mayday_id', $values['main_mayday_id'])
      ->set('main_mayday_minor_id', $values['main_mayday_minor_id'])
      ->set('main_mayday_kids_id', $values['main_mayday_kids_id'])
      ->set('main_ddt_id', $values['main_ddt_id'])
      ->set('main_sub_id', $values['main_sub_id'])
      ->set('main_anathi_id', $values['main_anathi_id'])
      ->set('wap_id', $values['wap_id'])
      ->set('wap_comp_id', $values['wap_comp_id'])
      ->set('vp_id', $values['vp_id'])
      ->set('vp_comp_id', $values['vp_comp_id'])
      ->save();

    $this->configFactory->getEditable('afrikaburn_shared.tickets')
      ->set('tickets', $values['tickets'])
      ->save();

    drupal_set_message('Settings saved');
  }
}
