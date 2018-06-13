<?php

/**
 * @file
 * Contains afrikaburn_shared form utilities
 */

/**
 * Gets a registration and title
 */
function _get_registration(){

  static $registration;
  static $registration_title;

  if (!isset($registration)) {
    $registration = \Drupal::routeMatch()->getParameter('node');
    if ($registration) {
      $registration_title = $registration->getTitle();
    }
  }

  return [$registration, $registration_title];
}

/**
 * Get a collective and title
 */
function _get_collective($registration){

  static $collective;
  static $collective_title;

  if (!isset($collective)) {

    $cid = array_values(array_filter(
      [
        \Drupal::request()->query->get('field_collective'),
        $registration && $registration->field_collective
          ? $registration->field_collective->getValue()[0]['target_id']
          : FALSE,
      ]
    ));

    if (isset($cid[0])) {
      $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid[0]);
      $collective_title = '<div class="subtitle">by the ' . $collective->getTitle() . ' collective</div>';
    }
  }

  return [$collective, $collective_title];
}

/**
 * Sets registration form titles
 */
function _set_registration_form_title(&$form, $form_id, $collective_title, $registration_title){

  $titles = [
    'node_theme_camps_form' => t('Register a Theme Camp') . $collective_title,
    'node_art_form' => t('Register an Artwork') . $collective_title,
    'node_performances_form' => t('Register a Performance') . $collective_title,

    'node_theme_camps_edit_form' => t('Updating %title Theme Camp registration', ['%title' => $registration_title]) . $collective_title,
    'node_art_edit_form' => t('Updating %title Artwork registration', ['%title' => $registration_title]) . $collective_title,
    'node_performances_edit_form' => t('Updating %title Performance registration', ['%title' => $registration_title]) . $collective_title,

    'node_art_grant_form' => t('Grant application for %title', ['%title' => $registration_title]) . $collective_title,
    'node_performances_grant_form' => t('Grant application for %title', ['%title' => $registration_title]) . $collective_title,
  ];

  if (isset($titles[$form_id])){
    $form['#title'] = [
      '#type' => 'markup',
      '#markup' => $titles[$form_id],
    ];
  }
}

/**
 * Sets form defaults for registration forms
 */
function _set_registration_form_defaults(&$form, $form_state, $form_id, $user, $collective){
  foreach(['node_art_', 'node_performances_', 'node_mutant_vehicles_', 'node_theme_camps_'] as $parent) {
    if (substr($form_id, 0, strlen($parent)) === $parent) {

      $form['revision_log']['#access'] = $user->hasRole('administrator');
      $form['field_collective']['#access'] = $user->hasRole('administrator');

      if ($collective) {
        $form['field_collective']['widget'][0]['target_id']['#default_value'] = $collective;
      } else {
        $registration = $form_state->getFormObject()->getEntity();
        if (isset($registration->field_collective) && !$registration->field_collective->getValue()[0]['target_id']) {
          throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
        }
      }
    }
  }
}

/**
 * Sets collective form defaults
 */
function _set_collective_defaults(&$form, $form_id, $user){
  if (substr($form_id, 0, strlen('node_collective')) === 'node_collective') {
    $form['field_col_admins']['#access'] = $user->hasRole('administrator');
    $form['field_col_members']['#access'] = $user->hasRole('administrator');
    if ($form_id == 'node_collective_form'){
      $form['field_col_admins']['widget'][0]['target_id']['#default_value'] = $user;
      $form['field_col_members']['widget'][0]['target_id']['#default_value'] = $user;
    }
  }
}
