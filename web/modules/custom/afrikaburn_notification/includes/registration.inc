<?php

/**
 * @file Contains afrikaburn registration notification methods.
 */

use \Drupal\user\Entity\User;

/**
 * Notifies registration stakeholders if needed
 */
function _notify_registration($entity){

  $bundle = $entity->bundle();

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $user = User::load(\Drupal::currentUser()->id());
  $collective = _meta_collective($entity);
  $settings = \Drupal::config('afrikaburn_notification.settings');
  $address_settings = \Drupal::config('afrikaburn_notification.address_settings');

  $stage = $entity->get('field_form_mode')->value;
  $complete = array_column($entity->get('field_prjr_complete')->getValue(), 'value');
  $state = in_array($stage, $complete) ? 'update' : 'new';

  // Mail collective
  if ($settings->get("$bundle-$stage-$state-collective-enabled")) {
    _notify(
      'no-reply@afrikaburn.com',
      $settings->get($bundle . '-label'),
      $address_settings->get($bundle . '-address'),
      $collective['members'] . ',' . $settings->get('archive'),
      $settings->get("$bundle-$stage-$state-collective-subject"),
      $settings->get("$bundle-$stage-$state-collective-body"),
      $entity
    );
  }

  // Mail wranglers
  if ($settings->get("$bundle-$stage-$state-wranglers-enabled")) {
    _notify(
      $address_settings->get($bundle . '-address'),
      $user->field_first_name->value . " '" . $user->name->value . "' " . $user->field_last_name->value . ' via AfrikaBurn Tribe',
      $user->mail->value,
      $settings->get('archive'),
      $settings->get("$bundle-$stage-$state-wranglers-subject"),
      $settings->get("$bundle-$stage-$state-wranglers-body"),
      $entity
    );
  }
}
