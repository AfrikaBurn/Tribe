<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_shared\Forms\AfrikaBurnSettings.
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
    $quickstart = $this->config('afrikaburn_shared.quickstart');

    // module_load_include('inc', 'afrikaburn_shared', 'includes/shared.quicket');

    $form['tabs'] = [
      '#type' => 'horizontal_tabs',
      '#entity_type' => 'config',
      '#group_name' => 'settings_tabs',
      '#bundle' => 'none',

      'quickstart' => [
        '#title' => 'Quickstart',
        '#type' => 'details',
        '#open' => TRUE,
        '#tree' => FALSE,
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

        'quickstart' => [
          '#title' => 'Quick start block HTML',
          '#type' => 'text_format',
          '#default_value' => $quickstart->get('quickstart')['value'],
          '#format' => $quickstart->get('quickstart')['format'],
        ],
      ],

      'quicket' => [
        '#title' => 'Quicket',
        '#type' => 'details',
        '#open' => TRUE,
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

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
            '#title' => 'General',
            '#default_value' => $config->get('main_general_id'),
          ],
          'main_general_minor_id' => [
            '#type' => 'textfield',
            '#title' => 'General Minor',
            '#default_value' => $config->get('main_general_minor_id'),
          ],
          'main_general_kids_id' => [
            '#type' => 'textfield',
            '#title' => 'General Kids',
            '#default_value' => $config->get('main_general_kids_id'),
            '#suffix' => '<br />',
          ],

          'main_mayday_id' => [
            '#type' => 'textfield',
            '#title' => 'Mayday',
            '#default_value' => $config->get('main_mayday_id'),
          ],
          'main_mayday_minor_id' => [
            '#type' => 'textfield',
            '#title' => 'Mayday Minor',
            '#default_value' => $config->get('main_mayday_minor_id'),
          ],
          'main_mayday_kids_id' => [
            '#type' => 'textfield',
            '#title' => 'Mayday Kids',
            '#default_value' => $config->get('main_mayday_kids_id'),
            '#suffix' => '<br />',
          ],

          'main_ddt_id' => [
            '#type' => 'textfield',
            '#title' => 'Direct Distribution',
            '#default_value' => $config->get('main_ddt_id'),
            '#suffix' => '<br />',
          ],

          'main_sub_id' => [
            '#type' => 'textfield',
            '#title' => 'Subsidised',
            '#default_value' => $config->get('main_sub_id'),
          ],

          'main_anathi_id' => [
            '#type' => 'textfield',
            '#title' => 'Anathi',
            '#default_value' => $config->get('main_anathi_id'),
            '#suffix' => '<br />',
          ],
        ],

        [
          '#type' => 'fieldset',
          '#tree' => FALSE,
          '#title' => 'WAPs',
          'wap_id' => [
            '#type' => 'textfield',
            '#title' => 'Event ID',
            '#default_value' => $config->get('wap_id'),
            '#attributes' => ['placeholder' => 'Using main Event ID'],
          ],
          'wap_comp_id' => [
            '#type' => 'textfield',
            '#title' => 'WAP',
            '#default_value' => $config->get('wap_comp_id'),
          ],
        ],

        [
          '#type' => 'fieldset',
          '#tree' => FALSE,
          '#title' => 'VPs',
          'vp_id' => [
            '#type' => 'textfield',
            '#title' => 'Event ID',
            '#default_value' => $config->get('vp_id'),
            '#attributes' => ['placeholder' => 'Using main Event ID'],
          ],
          'vp_comp_id' => [
            '#type' => 'textfield',
            '#title' => 'VP',
            '#default_value' => $config->get('vp_comp_id'),
          ],
        ],
      ],

      'tickets' => [
        '#title' => 'Sales',
        '#type' => 'details',
        '#open' => TRUE,
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

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
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

        'batch-size' => ['#type' => 'select', '#title' => 'Batch size', '#options' => array_combine(range(500, 10000, 500), range(500, 10000, 500))],
        ['#type' => 'submit', '#value' => 'Add AfrikaBurn Members'],
        // ['#type' => 'submit', '#value' => 'Regenerate Quicket data'],
        // ['#type' => 'submit', '#value' => 'Resave Users'],
        ['#type' => 'submit', '#value' => 'Wipe Quicket data'],
        // ['#type' => 'submit', '#value' => 'Migrate Collectives'],
      ],
    ];

    $events = QuicketController::getEvents();
    foreach($events as $id=>$event){
      $form['tabs']['quicket']['defined'][$id] = [
        '#type' => 'details',
        '#open' => FALSE,
        '#title' => $event->name,
        'description' => ['#markup' => $event->description, '#weight' => 1],
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
        UpdateController::resaveUsers($form_state->getValue('batch-size'));
      break;
      case 'Wipe Quicket data':
        UpdateController::wipeQuicket();
      break;
      case 'Regenerate Quicket data':
        UpdateController::regenerateQuicketData($form_state->getValue('batch-size'));
      break;
      case 'Migrate Collectives':
        UpdateController::migrateCollectives();
      break;
      case 'Add AfrikaBurn Members':
        UpdateController::addTribeMembers($form_state->getValue('batch-size'));
      break;
      default:
        $this->configFactory->getEditable('afrikaburn_shared.quickstart')
          ->set('quickstart', $values['quickstart'])
          ->save();
        $this
          ->configFactory->getEditable('afrikaburn_shared.settings')
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
          ->set('tickets', $values['tickets'])
          ->save();
        drupal_set_message('Settings saved');
      break;
    }
  }
}