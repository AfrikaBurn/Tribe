<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_registration\Forms\RegistrationSettings.
 */

namespace Drupal\afrikaburn_registration\Forms;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class RegistrationSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_registration_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'afrikaburn_registration.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    module_load_include('inc', 'afrikaburn_registration', 'includes/form');

    $config = $this->config('afrikaburn_registration.settings');
    $user = \Drupal::currentUser();

    foreach(_project_form_modes() as $key=>$map){

      $project = $map['title'];
      $modes = $map['modes'];

      $form[$key] = [
        '#type' => 'fieldset',
        '#title' => $project,
        '#tree' => TRUE,
      ];

      foreach($modes as $mode){
        $form[$key][$mode] = [
          '#type' => 'checkbox',
          '#title' => str_replace('_', ' ', $mode),
          '#default_value' => $config->get($key . '/' . $mode),
        ];
      }

    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    module_load_include('inc', 'afrikaburn_registration', 'includes/form');

    $values = $form_state->getValues();

    foreach(_project_form_modes() as $key=>$map){

      $project = $map['title'];
      $modes = $map['modes'];

      foreach($modes as $mode){
        $this
          ->config('afrikaburn_registration.settings')
          ->set($key . '/' . $mode, $values[$key][$mode]);
      }

    }

    $this->config('afrikaburn_registration.settings')->save();
    drupal_set_message('Settings saved');
  }

}