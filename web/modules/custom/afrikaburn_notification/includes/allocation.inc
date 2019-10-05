<?php

/**
 * @file Contains afrikaburn allocation notification methods.
 */

use \Drupal\user\Entity\User;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;


/**
 * Notifies allocation stakeholders if needed
 */
function _notify_allocation($entity){

  $bundle = $entity->bundle();

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $user = User::load(\Drupal::currentUser()->id());
  $settings = \Drupal::config('afrikaburn_notification.settings');
  $address_settings = \Drupal::config('afrikaburn_notification.address_settings');
  $registration = \Drupal::routeMatch()->getParameter('node');
  $collective = $registration->field_collective->entity;
  $stage = $entity->bundle() . 's';
  $bundle = $registration->bundle();
  $to = $entity->field_team_member->entity->field_email->value;

  // Mail collective
  if (
    $settings->get("$bundle-$stage-allocation-recipients-enabled") &&
    CollectiveController::setting($collective, 'emails')
  ) {
    _notify(
      $to,
      $settings->get($bundle . '-label'), $address_settings->get($bundle . '-address'),
      $settings->get('archive'),
      $settings->get("$bundle-$stage-allocation-recipients-subject"),
      $settings->get("$bundle-$stage-allocation-recipients-body"),
      $registration
    );
  }

  // Mail wranglers
  if ($settings->get("$bundle-$stage-allocation-wranglers-enabled")) {
    _notify(
      $address_settings->get($bundle . '-address'),
      $user->field_first_name->value . " '" . $user->name->value . "' " . $user->field_last_name->value . ' via AfrikaBurn Tribe', 'no-reply@afrikaburn.com',
      $settings->get('archive'),
      $settings->get("$bundle-$stage-allocation-wranglers-subject"),
      $settings->get("$bundle-$stage-allocation-wranglers-body"),
      $registration
    );
  }
}