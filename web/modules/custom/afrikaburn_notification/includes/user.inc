<?php

/**
 * @file Contains afrikaburn account notification methods.
 */


/**
 * Notifies archive of account creation
 */
function _notify_account($entity){

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $address_settings = \Drupal::config('afrikaburn_notification.address_settings');

  _notify(
    'no-reply@afrikaburn.com',
    'Afrikaburn Tribe',
    $address_settings->get('archive'),
    '',
    'New user account created: ' . $entity->get('field_first_name')->value . ' "' . $entity->get('name')->value . '" ' . $entity->get('field_last_name')->value,
    render(
      \Drupal::entityTypeManager()
        ->getViewBuilder('user')
        ->view($entity, 'mail')
    ),
    $entity
  );
}