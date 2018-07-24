<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_notification\Forms\NotificationSettings.
 */

namespace Drupal\afrikaburn_notification\Forms;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class NotificationSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_notification_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'afrikaburn_notification.settings',
      'afrikaburn_addresses.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    module_load_include('inc', 'afrikaburn_registration', 'includes/form');

    $settings = $this->config('afrikaburn_notification.settings');
    $addresses = $this->config('afrikaburn_addresses.settings');
    $user = \Drupal::currentUser();

    $form = [
      'addresses' => [
        '#type' => 'fieldset',
        '#title' => 'Archive address',
      ],
    ];

    $form['addresses']['archive'] = [
      '#type' => 'email',
      '#default_value' => $addresses->get('archive'),
    ];

    foreach(_project_form_modes() as $key=>$project){

      $form[$key] = [
        '#type' => 'fieldset',
        '#title' => $project['title'] . ' messages',
      ];

      $form[$key]['from'] = [
        '#type' => 'container',
        '#attributes' => ['class' => array('container-inline')],
      ];
      $form[$key]['from'][$key . '-label'] = [
        '#type' => 'textfield',
        '#title' => 'From',
        '#placeholder' => 'Label to display as from',
        '#default_value' => $settings->get($key . '-label'),
      ];
      $form[$key]['from'][$key . '-address'] = [
        '#type' => 'email',
        '#placeholder' => 'Reply to email address',
        '#default_value' => $addresses->get($key . '-address'),
        '#prefix' => '&lt;',
        '#suffix' => '&gt;',
      ];

      foreach($project['modes'] as $mode=>$label){
        $form[$key][$mode] = [
          '#type' => 'details',
          '#title' => $label,
        ];

        foreach(['new', 'update'] as $cycle){

          $form[$key][$mode][$cycle] = [
            '#type' => 'fieldset',
            '#title' => $cycle,
            '#attributes' => ['style' => ['display: inline-block; width: 46%;']],
          ];

          foreach(['collective', 'wranglers'] as $recipient){

            $parentage = implode('-', [$key, $mode, $cycle, $recipient]);

            $form[$key][$mode][$cycle][$recipient][$parentage . '-subject'] = [
              '#type' => 'textfield',
              '#title' => 'To ' . $recipient,
              '#default_value' => $settings->get($parentage . '-subject'),
              '#attributes' => [
                'style' => ['width: 100%;'],
                'placeholder' => 'Subject',
              ],
            ];
            $form[$key][$mode][$cycle][$recipient][$parentage . '-body'] = [
              '#type' => 'textarea',
              '#default_value' => $settings->get($parentage . '-body'),
              '#rows' => 20,
              '#attributes' => [
                'placeholder' => 'Body',
              ],
            ];
          }
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $config = $this->config('afrikaburn_notification.settings');
    $addresses = $this->config('afrikaburn_addresses.settings');

    // Don't save form metadata
    foreach([
      'submit',
      'form_build_id',
      'form_token',
      'form_id',
      'op',
    ] as $key) unset($values[$key]);

    // Save addressing seperately - we don't want them to move between environments
    foreach(
      [
        'archive',
        'art-address',
        'performances-address',
        'theme-camps-address',
        'mutant-vehicles-address',
      ] as $key
    ){
      $addresses->set($key, $values[$key]);
      unset($values[$key]);
    }

    // All the other settings we do want to move
    foreach($values as $key=>$value){
      $config->set($key, $value);
    }

    $config->save();
    $addresses->save();
    drupal_set_message('Settings saved');
  }

}