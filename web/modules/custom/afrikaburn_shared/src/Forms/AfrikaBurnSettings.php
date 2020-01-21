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
      'afrikaburn_shared.quickstart',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    $settings = $this->config('afrikaburn_shared.settings');
    $quickstart = $this->config('afrikaburn_shared.quickstart');
    $webforms = \Drupal::entityTypeManager()->getStorage('webform')->loadMultiple();

    $form['tabs'] = [
      '#type' => 'horizontal_tabs',
      '#entity_type' => 'config',
      '#group_name' => 'settings_tabs',
      '#bundle' => 'none',

      'quickstart' => [
        '#title' => 'Quickstart',
        '#type' => 'details',
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

      'actions' => [
        '#title' => 'Maintenance Tasks',
        '#type' => 'details',
        '#open' => TRUE,
        '#entity_type' => 'config',
        '#group_name' => 'settings_tabs',
        '#bundle' => 'none',

        'wipe' => [
          '#type' => 'details',
          '#title' => 'Wipe Quicket Data',
          'text' => ['#markup' => '<p>DANGER! DANGER! <ul><li>Can not be undone!</li><li>Requires regeneration afterwards.</li></ul></p>'],
          ['#type' => 'submit', '#value' => 'I know what I\'m doing, Wipe!'],
        ],

        'regenerate' => [
          '#type' => 'details',
          '#title' => 'Regenerate Quicket Data',
          'text' => ['#markup' => '<p>DANGER! DANGER! <ul><li>Disable mail first.</li><li>Warn Quicket!</li><li>This will take forever...</li></ul></p>'],
          ['#type' => 'submit', '#value' => 'I know what I\'m doing, Regenerate!'],
        ],

        'resave' => [
          '#type' => 'details',
          '#title' => 'Resave all users',
          'text' => ['#markup' => '<p>DANGER! DANGER! <ul><li>Disable mail first.</li><li>Warn Quicket!</li><li>This will take a while...</li></ul></p>'],
          ['#type' => 'submit', '#value' => 'I know what I\'m doing, Resave!'],
        ],

        'assimilate' => [
          '#type' => 'details',
          '#title' => 'Assimilate AfrikaBurn Members',
          'text' => ['#markup' => '<p>DANGER! DANGER! <ul><li>Disable mail first.</li><li>This may take a while...</li></ul></p>'],
          ['#type' => 'submit', '#value' => 'I know what I\'m doing, Assimilate!'],
        ],

        'resave-webform-results' => [
          '#type' => 'details',
          '#title' => 'Resave Webform Results',
          'webform' => [
            '#title' => 'Webform:',
            '#type' => 'select',
            '#options' => array_map(
              function($webform){ return $webform->get('title'); },
              $webforms
            ),
          ],
          ['#type' => 'submit', '#value' => 'Resave results!'],
        ],

        'batch-size' => [
          '#type' => 'select',
          '#title' => 'Batch size',
          '#default_value' => $settings->get('batch-size'),
          '#options' => array_combine(
            range(500, 10000, 500),
            range(500, 10000, 500)
          )
        ],
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $actions = $form['tabs']['actions'];

    switch($values['op']){

      case $actions['wipe'][0]['#value']:
        UpdateController::wipeQuicket();
      break;
      case $actions['regenerate'][0]['#value']:
        UpdateController::regenerateQuicketData($values['batch-size']);
      break;
      case $actions['resave'][0]['#value']:
        UpdateController::resaveUsers($values['batch-size']);
      break;
      case $actions['assimilate'][0]['#value']:
        UpdateController::addTribeMembers($values['batch-size']);
      break;
      case $actions['resave-webform-results'][0]['#value']:
        UpdateController::resaveWebformResults($values['webform'], $values['batch-size']);
      break;

      default:
        $this->configFactory->getEditable('afrikaburn_shared.quickstart')
          ->set('quickstart', $values['quickstart'])
          ->save();

        $this->configFactory->getEditable('afrikaburn_shared.settings')
          ->set('batch-size', $values['batch-size'])
          ->save();

        drupal_set_message('Settings saved');
      break;
    }
  }
}
