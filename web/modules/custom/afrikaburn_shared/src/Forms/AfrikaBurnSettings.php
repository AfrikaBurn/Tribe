<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_shared\Forms\AfrikaBurnSettings.
 */

namespace Drupal\afrikaburn_shared\Forms;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class AfrikaBurnSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_shared_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'afrikaburn_shared.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    $config = $this->config('afrikaburn_shared.settings');

    module_load_include('inc', 'afrikaburn_shared', 'includes/shared.quicket');

    $form['tabs'] = [
      '#type' => 'horizontal_tabs',

      'quicket' => [
        '#title' => 'Quicket',
        '#type' => 'details',
        '#open' => TRUE,

        'defined' => [
          '#type' => 'fieldset',
          '#title' => 'Defined events',
        ],

        [
          '#type' => 'fieldset',
          '#tree' => FALSE,
          '#title' => 'Main event',
          'main_id' => [
            '#type' => 'textfield',
            '#title' => 'Event ID',
            '#default_value' => $config->get('main_id'),
            '#suffix' => '<br />',
          ],

          'main_general_id' => [
            '#type' => 'textfield',
            '#title' => 'General ticket ID',
            '#default_value' => $config->get('main_general_id'),
          ],
          'main_general_minor_id' => [
            '#type' => 'textfield',
            '#title' => 'General Minor ticket ID',
            '#default_value' => $config->get('main_general_minor_id'),
          ],
          'main_general_kids_id' => [
            '#type' => 'textfield',
            '#title' => 'General Kids ticket ID',
            '#default_value' => $config->get('main_general_kids_id'),
            '#suffix' => '<br />',
          ],

          'main_mayday_id' => [
            '#type' => 'textfield',
            '#title' => 'Mayday ticket ID',
            '#default_value' => $config->get('main_mayday_id'),
          ],
          'main_mayday_minor_id' => [
            '#type' => 'textfield',
            '#title' => 'Mayday Minor ticket ID',
            '#default_value' => $config->get('main_mayday_minor_id'),
            '#suffix' => '<br />',
          ],

          'main_ddt_id' => [
            '#type' => 'textfield',
            '#title' => 'Direct Distribution ticket ID',
            '#default_value' => $config->get('main_ddt_id'),
            '#suffix' => '<br />',
          ],

          'main_sub_id' => [
            '#type' => 'textfield',
            '#title' => 'Subsidised ticket ID',
            '#default_value' => $config->get('main_sub_id'),
          ],

          'main_anathi_id' => [
            '#type' => 'textfield',
            '#title' => 'Anathi ticket ID',
            '#default_value' => $config->get('main_anathi_id'),
            '#suffix' => '<br />',
          ],
        ],

        [
          '#type' => 'fieldset',
          '#tree' => FALSE,
          '#title' => 'WAP event',
          'wap_id' => [
            '#type' => 'textfield',
            '#title' => 'Event ID',
            '#default_value' => $config->get('wap_id'),
          ],
          'wap_comp_id' => [
            '#type' => 'textfield',
            '#title' => 'WAP ticket ID',
            '#default_value' => $config->get('wap_comp_id'),
          ],
        ],

        [
          '#type' => 'fieldset',
          '#tree' => FALSE,
          '#title' => 'VP event',
          'vp_id' => [
            '#type' => 'textfield',
            '#title' => 'Event ID',
            '#default_value' => $config->get('vp_id'),
          ],
          'vp_comp_id' => [
            '#type' => 'textfield',
            '#title' => 'VP ticket ID',
            '#default_value' => $config->get('vp_comp_id'),
          ],
        ],
      ],

      'tickets' => [
        '#title' => 'Sales',
        '#type' => 'details',
        '#open' => TRUE,
        'tickets' => [
          '#type' => 'checkboxes',
          '#title' => 'Open Ticket Sales',
          '#options' => [
            'general' => 'General',
            'anathi' => 'Anathi',
          ],
          '#default_value' => $config->get('tickets'),
        ],
      ],

      'actions' => [
        '#title' => 'Actions',
        '#type' => 'details',
        '#open' => TRUE,
        'content' => [
          '#type' => 'list',
          ['#type' => 'submit', '#value' => 'Resave Users', '#prefix' => '<br />'],
          ['#type' => 'submit', '#value' => 'Wipe Quicket data', '#prefix' => '<br />'],
          ['#type' => 'submit', '#value' => 'Regenerate Quicket data', '#prefix' => '<br />'],
          ['#type' => 'submit', '#value' => 'Migrate Collectives', '#prefix' => '<br />'],
          ['#type' => 'submit', '#value' => 'Add AfrikBurn Members', '#prefix' => '<br />'],
        ],
      ],
    ];

    $events = quicket_get_events();
    foreach($events as $id=>$event){
      $form['tabs']['quicket']['defined'][$id] = [
        '#type' => 'details',
        '#open' => FALSE,
        '#title' => $event->name,
        'description' => ['#markup' => $event->description],
        'id' => ['#title' => 'Event ID', '#type' => 'textfield', '#value' => $event->id, '#attributes' => ['disabled' => 'disabled']],
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

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    switch($values['op']){
      case 'Resave Users':
        \Drupal\afrikaburn_shared\Controller\UpdateController::resaveUsers();
      break;
      case 'Wipe Quicket data':
        \Drupal\afrikaburn_shared\Controller\UpdateController::wipeQuicket();
      break;
      case 'Regenerate Quicket data':
        \Drupal\afrikaburn_shared\Controller\UpdateController::regenerateQuicketData();
      break;
      case 'Migrate Collectives':
        \Drupal\afrikaburn_shared\Controller\UpdateController::migrateCollectives();
      break;
      case 'Add AfrikBurn Members':
        \Drupal\afrikaburn_shared\Controller\UpdateController::addTribeMembers();
      break;
      default:
        $this
          ->configFactory->getEditable('afrikaburn_shared.settings')
          ->set('main_id', $values['main_id'])
          ->set('main_general_id', $values['main_general_id'])
          ->set('main_general_minor_id', $values['main_general_minor_id'])
          ->set('main_general_kids_id', $values['main_general_kids_id'])
          ->set('main_mayday_id', $values['main_mayday_id'])
          ->set('main_ddt_id', $values['main_ddt_id'])
          ->set('main_sub_id', $values['main_mayday_id'])
          ->set('main_anathi_id', $values['main_mayday_id'])
          ->set('wap_id', $values['wap_id'])
          ->set('wap_comp_id', $values['wap_comp_id'])
          ->set('vp_id', $values['vp_id'])
          ->set('vp_comp_id', $values['vp_comp_id'])
          ->set('tickets', $values['tickets'])
          ->save();
        drupal_set_message('Settings saved');
      break;
    }
  }
}