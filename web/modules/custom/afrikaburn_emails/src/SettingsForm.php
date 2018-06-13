<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_emails\SettingsForm.
 */

namespace Drupal\afrikaburn_emails;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines a form that configures forms module settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_emails_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'afrikaburn_emails.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
  
    $config = $this->config('afrikaburn_emails.settings');
    $user = \Drupal::currentUser();
    $message_definition = $config->get('message_definition');

    $form['message_definition'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message definition'),
      '#description' => "List one definition per line in the format:<br />
        name:operation:entity_type[:bundle[:field]]|Subject|recipient,recipient,...<br />
        Where:<br />
        name = unique name of this mail<br />
        operation = [create|update|delete]<br />
        entity_type = [node|user|...]<br />
        bundle = [theme_camp|mutant_vehicle|...]<br />
        field = List of & delimited fields that must have a value (prepend with ! to negate)<br />
        recipients = [author|group|wrangler|...]<br />
        Eg.<br />
        up_0:update:user|Your account has been changed|author<br />
        cr_0:create:node:page|Your new page has been created|author<br />
        cr_1:create:node:page|A new page has been created|group<br />
        cr_2:create:node:page:field_name|A new page with a field_name field value has been created|group<br />
        cr_3:create:node:page:field_name&!field_name2|A new page with a field_name and not a field_name2 field value has been created|group",
      '#default_value' => $message_definition,
    ];

    $form[] = [
      '#markup' => '<h3>Message templates</h3>',
    ];

    try{
      if (strlen($message_definition)){
        $definition_pairs = explode("\n", $message_definition);
        if (is_array($definition_pairs)){
          foreach($definition_pairs as $key_label){
            list($key, $label, $recipient) = explode('|', $key_label);
            $key_parts = explode(':', $key);
            $form[$key_parts[0]] = [
              '#type' => 'textarea',
              '#title' => $label . ' [message to ' . trim($recipient) . ']',
              '#default_value' => $config->get($key_parts[0]),
              '#attributes' => [
                'rows' => 20,
              ],
              '#access' => $user->hasPermission('edit ' . str_replace(':', ' ', $key) . ' template'),
              '#description' => 'Available tokens: [' . $key_parts[2] . ':...]',
            ];
          }
        }
      }
    } catch (Exception $e) {
      dsm('Error in message definition, please check the configuration.');
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('afrikaburn_emails.settings')
      ->set('message_definition', trim($values['message_definition']))
      ->save();
    $definition_pairs = explode("\n", trim($values['message_definition']));
    if (is_array($definition_pairs)){
      foreach($definition_pairs as $key_label){
        list($key, $label, $recipient) = explode('|', $key_label);
        $key = explode(':', $key)[0];
        $this->config('afrikaburn_emails.settings')
          ->set($key, $values[$key])
          ->save();
      }
    }
  }

}