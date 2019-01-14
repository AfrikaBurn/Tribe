<?php
/**
 * @file: form.inc
 */


 /**
  * Determine which forms have which modes
  */
function _project_form_modes(){
  return [

    'art' => [
      'title' => 'Artwork',
      'modes' => [
        'form_1' => 'Registration form 1',
        'form_2' => 'Registration form 2',
        'form_3' => 'Registration form 3',
        'grant' => 'Grant application',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'wap' => 'Work access passes',
      ],
    ],

    'performances' => [
      'title' => 'Binnekring event',
      'modes' => [
        'form_1' => 'Registration form',
        'grant' => 'Grant application',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'wap' => 'Work access passes',
      ],
    ],

    'mutant_vehicles' => [
      'title' => 'Mutant Vehicle',
      'modes' => [
        'form_1' => 'Registration form',
        'grant' => 'Grant application',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'wap' => 'Work access passes',
      ],
    ],

    'theme_camps' => [
      'title' => 'Theme Camp',
      'modes' => [
        'form_1' => 'Registration form 1',
        'form_2' => 'Registration form 2',
        'form_3' => 'Registration form 3',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'wap' => 'Work access passes',
      ],
    ],
  ];
}

/**
 * Form meta data.
 */
function _project_form_config() {
  return [

    /* ---- Art ---- */
    'node_art_form' => [
      'mode' => 'new',
      'title' => 'Register',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_edit_form' => [
      'mode' => 'edit',
      'title' => 'Updating: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_form_1_form' => [
      'mode' => 'form_1',
      'title' => 'Registering: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_form_2_form' => [
      'mode' => 'form_2',
      'title' => 'Continuing registration: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_form_3_form' => [
      'mode' => 'form_3',
      'title' => 'Completing registration: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_grant_form' => [
      'mode' => 'grant',
      'title' => 'Grant application for: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    //TODO: Suppliers
    //TODO: WAP

    /* ---- Performance ---- */

    'node_performances_form' => [
      'mode' => 'new',
      'title' => 'Register',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    'node_performances_edit_form' => [
      'mode' => 'edit',
      'title' => 'Updating: %title',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    'node_performances_form_1_form' => [
      'mode' => 'form_1',
      'title' => 'Registering: %title',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    'node_performances_grant_form' => [
      'mode' => 'grant',
      'title' => 'Grant application for: %title',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    //TODO: Suppliers
    //TODO: WAP

    /* ---- Mutant Vehicles ---- */

    'node_mutant_vehicles_form' => [
      'mode' => 'new',
      'title' => 'Register',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    'node_mutant_vehicles_edit_form' => [
      'mode' => 'edit',
      'title' => 'Updating: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    'node_mutant_vehicles_form_1_form' => [
      'mode' => 'form_1',
      'title' => 'Registering: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    'node_mutant_vehicles_grant_form' => [
      'mode' => 'grant',
      'title' => 'Grant application for: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    //TODO: Suppliers
    //TODO: WAP

    /* ---- Theme Camps ---- */

    'node_theme_camps_form' => [
      'mode' => 'edit',
      'title' => 'Register',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_edit_form' => [
      'mode' => 'edit',
      'title' => 'Updating: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_form_1_form' => [
      'mode' => 'form_1',
      'title' => 'Registering: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_form_2_form' => [
      'mode' => 'form_2',
      'title' => 'Continuing registration: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_form_3_form' => [
      'mode' => 'form_3',
      'title' => 'Completing registration: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_support_camp_form' => [
      'mode' => 'support_camp',
      'title' => 'Registering: %title',
      'type' => 'a Support Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
  ];
}

/**
 * Checks whether the form is open or the user is a wrangler.
 */
function _check_access($defaults, $user, $form_state){
  // Redirect on closed form
  $config = \Drupal::config('afrikaburn_registration.settings');
  $wrangler = $user->hasRole($defaults['wrangler']);
  $bundle = $form_state->getFormObject()->getEntity()->bundle();
  $open = $config->get($bundle . '/' . $defaults['mode']);
  if (!$open && !$wrangler) {
    drupal_set_message($defaults['title'] . ' is closed!', 'warning', true);
    $response = new Symfony\Component\HttpFoundation\RedirectResponse(\Drupal::url('<front>'));
    $response->send();
  }
}

/**
 * Retrieve the collective from the URL or the registration.
 */
function _get_collective($registration){
  $cid = \Drupal::request()->query->get('field_collective')
    ? \Drupal::request()->query->get('field_collective')
    : ($registration && $registration->field_collective
      ? $registration->field_collective->getValue()[0]['target_id']
      : false);
  return \Drupal::entityTypeManager()->getStorage('node')->load($cid);
}

/**
 * Retrieve the collective from the URL or the registration.
 */
function _get_supporting_project($registration){
  $cid = \Drupal::request()->query->get('field_supporting_project')
    ? \Drupal::request()->query->get('field_supporting_project')
    : ($registration && $registration->field_supporting_project
      ? $registration->field_supporting_project->getValue()[0]['target_id']
      : false);
  return \Drupal::entityTypeManager()->getStorage('node')->load($cid);
}

/**
 * Generate a form title based on the collective, registration and type.
 */
function _get_title($defaults, $collective, $registration){

  $collective_title = ' <div class="subtitle">' . $defaults['type'] . ' by the ' . $collective->getTitle() . ' collective</div>';

  return t(
    $defaults['title'],
    ['%title' => $registration ? $registration->getTitle() : '']
  ) . $collective_title;
}
