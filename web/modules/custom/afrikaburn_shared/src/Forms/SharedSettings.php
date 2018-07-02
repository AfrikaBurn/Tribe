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
      'new_cycle' => [
        '#type' => 'date',
        '#title' => 'Calculate new cycle stats from',
        '#default_value' => $config->get('new_cycle'),
      ]
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    $this
      ->config('afrikaburn_shared.settings')
      ->set('new_cycle', $values['new_cycle'])->save();

    drupal_set_message('Settings saved');
  }

}