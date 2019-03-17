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

    $form['tickets'] = [
      '#type' => 'checkboxes',
      '#title' => 'Open Ticket Sales',
      '#options' => [
        'general' => 'General',
        'anathi' => 'Anathi',
      ],
      '#default_value' => $config->get('tickets'),
    ];

    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'collective')
      ->condition('title', 'AfrikaBurn')
      ->execute();

    if (!count($nids)){
      $form['actions'][] = [
        '#type' => 'submit',
        '#value' => 'Update Collectives'
      ];
    }

    $form['actions'][] = [
      '#type' => 'submit',
      '#value' => 'Resave Users'
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    switch($values['op']){
      case 'Update Collectives':
        \Drupal\afrikaburn_collective\Controller\UpdateController::update();
      break;
      case 'Resave Users':
        \Drupal\afrikaburn_collective\Controller\UpdateController::resave();
      break;
      default:
        $config = $this->config('afrikaburn_shared.settings');
        $config->set('tickets', $values['tickets']);
        $this->config('afrikaburn_shared.settings')->save();
        drupal_set_message('Settings saved');
      break;
    }
  }
}