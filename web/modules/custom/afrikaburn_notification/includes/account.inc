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
  $account = \Drupal::entityTypeManager()
    ->getViewBuilder('user')
    ->view($entity, 'mail');

  _notify(
    $address_settings->get('archive'),
    'Afrikaburn Tribe',
    'no-reply@afrikaburn.com',
    FALSE,
    'New user account created: ' . $entity->get('field_first_name')->value . ' "' . $entity->get('name')->value . '" ' . $entity->get('field_last_name')->value,
    render($account),
    $entity
  );
}

/**
 * Notifies archive of account deletion
 */
function _notify_delete($entity){

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $address_settings = \Drupal::config('afrikaburn_notification.address_settings');
  $account = \Drupal::entityTypeManager()
    ->getViewBuilder('user')
    ->view($entity, 'mail');

  _notify(
    $address_settings->get('archive'),
    'Afrikaburn Tribe',
    'no-reply@afrikaburn.com',
    FALSE,
    'New user account DELETED: ' . $entity->get('field_first_name')->value . ' "' . $entity->get('name')->value . '" ' . $entity->get('field_last_name')->value,
    render($account),
    $entity
  );
}