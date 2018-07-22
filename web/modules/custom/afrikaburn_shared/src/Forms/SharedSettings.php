<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_shared\Forms\SharedSettings.
 */

namespace Drupal\afrikaburn_shared\Forms;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class SharedSettings extends ConfigFormBase {

  private const ADRESSES = [
    'art' => 'Art',
    'performances' => 'Binnekring Events',
    'mutant_vehivles' => 'Mutant vehicles',
    'theme_camps' => 'Theme Camps',
    'archice' => 'Archive',
  ];

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
    $user = \Drupal::currentUser();

    $form = [
      'addresses' => [
        '#type' => 'fieldset',
        '#title' => 'Notification email addresses',
      ],
    ];

    foreach($this::ADRESSES as $key=>$title){
      $form['addresses'][$key] = [
        '#type' => 'email',
        '#title' => $title,
        '#default_value' => $config->get($key),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $config = $this->config('afrikaburn_shared.settings');

    foreach($this::ADRESSES as $key=>$title){
      $config->set($key, $values[$key]);
    }

    $config->save();
    drupal_set_message('Settings saved');
  }

}