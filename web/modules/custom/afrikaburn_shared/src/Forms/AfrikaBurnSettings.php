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

    $form['actions'][] = [
      '#type' => 'submit',
      '#value' => 'Resave Users'
    ];

    $form['actions'][] = [
      '#type' => 'submit',
      '#value' => 'Migrate Collectives'
    ];

    $form['actions'][] = [
      '#type' => 'submit',
      '#value' => 'Add AfrikBurn Members'
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    switch($values['op']){
      case 'Resave Users':
        \Drupal\afrikaburn_collective\Controller\UpdateController::resaveUsers();
      break;
      case 'Migrate Collectives':
        \Drupal\afrikaburn_collective\Controller\UpdateController::migrateCollectives();
      break;
      case 'Add AfrikBurn Members':
        \Drupal\afrikaburn_collective\Controller\UpdateController::addTribeMembers();
      break;
      default:
        $this
          ->configFactory->getEditable('afrikaburn_shared.settings')
          ->set('tickets', $values['tickets'])
          ->save();
        drupal_set_message('Settings saved');
      break;
    }
  }
}