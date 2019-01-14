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
      'afrikaburn_notification.address_settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    module_load_include('inc', 'afrikaburn_registration', 'includes/form');

    $settings = $this->config('afrikaburn_notification.settings');
    $addresses = $this->config('afrikaburn_notification.address_settings');
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

    $notifications = _project_form_modes() + [
      'collective' => [
        'title' => 'Collective',
        'modes' => [
          'collective' => 'Collective',
          'invitation' => 'Invitation',
        ],
      ],
    ];

    foreach($notifications as $key=>$project){

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

        if ($mode == 'support_camp' && $key == 'theme_camps' || $mode != 'support_camp'){

          $form[$key][$mode] = [
            '#type' => 'details',
            '#title' => $label,
          ];

          foreach(['new', 'update'] as $cycle){

            $form[$key][$mode][$cycle] = [
              '#type' => 'fieldset',
              '#title' => $cycle,
              '#attributes' => ['class' => ['settings-column']],
            ];

            $recipients = ['collective', $mode == 'invitation' ? 'invitees' : 'wranglers'];
            foreach($recipients as $recipient){

              $parentage = implode('-', [$key, $mode, $cycle, $recipient]);

              $form[$key][$mode][$cycle][$recipient][$parentage . '-enabled'] = [
                '#type' => 'checkbox',
                '#title' => 'Active',
                '#default_value' => $settings->get($parentage . '-enabled'),
                '#rows' => 20,
              ];
              $form[$key][$mode][$cycle][$recipient][$parentage . '-subject'] = [
                '#type' => 'textfield',
                '#title' => 'To ' . $recipient,
                '#default_value' => $settings->get($parentage . '-subject'),
                '#attributes' => [
                  'placeholder' => 'Subject',
                ],
              ];
              $form[$key][$mode][$cycle][$recipient][$parentage . '-body'] = [
                '#type' => 'text_format',
                '#default_value' => $settings->get($parentage . '-body'),
                '#rows' => 20,
                '#format' => 'full_html',
                '#base_type' => 'textarea',
                '#attributes' => [
                  'placeholder' => 'Body',
                ],
              ];
            }
          }
        }
      }
    }

    $form['#attached']['library'][] = 'afrikaburn_notification/settings';

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $config = $this->config('afrikaburn_notification.settings');
    $addresses = $this->config('afrikaburn_notification.address_settings');

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
        'mutant_vehicles-address',
        'theme_camps-address',
        'collective-address',
      ] as $key
    ){
      $addresses->set($key, $values[$key]);
      unset($values[$key]);
    }

    // All the other settings we do want to move
    foreach($values as $key=>$value){
      isset($value['value'])
        ? $config->set($key, $value['value'])
        : $config->set($key, $value);
    }

    $config->save();
    $addresses->save();
    drupal_set_message('Settings saved');
  }

}