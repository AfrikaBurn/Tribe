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
      'modes' => ['form_1', 'form_2', 'form_3', 'grant', 'support', 'supplier', 'wap'],
    ],

    'performances' => [
      'title' => 'Binnekring event',
      'modes' => ['form_1', 'form_2', 'form_3', 'grant', 'support', 'supplier', 'wap'],
    ],

    'mutant_vehicles' => [
      'title' => 'Mutant Vehicle',
      'modes' => ['form_1', 'grant', 'support', 'supplier', 'wap'],
    ],

    'theme_camps' => [
      'title' => 'Theme Camp',
      'modes' => ['form_1', 'form_2', 'form_3', 'grant', 'supplier', 'wap'],
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
    //TODO: Form 2
    //TODO: Form 3
    //TODO: Grant
    //TODO: Suppliers
    //TODO: WAP

    /* ---- Mutant Vehicles ---- */

    'node_mutant_vehicles_form' => [
      'mode' => 'new',
      'title' => 'Register',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicles_wrangler'
    ],
    'node_mutant_vehicles_edit_form' => [
      'mode' => 'edit',
      'title' => 'Updating: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicles_wrangler'
    ],
    'node_mutant_vehicles_form_1_form' => [
      'mode' => 'form_1',
      'title' => 'Registering: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicles_wrangler'
    ],
    //TODO: Form 2
    //TODO: Form 3
    //TODO: Grant
    //TODO: Suppliers
    //TODO: WAP

    /* ---- Performance ---- */

    //TODO: Edit
    //TODO: Form 1
    //TODO: Form 2
    //TODO: Form 3
    //TODO: Grant
    //TODO: Suppliers
    //TODO: WAP

    /* ---- Theme Camps ---- */

    'node_theme_camps_form' => [
      'mode' => 'edit',
      'title' => 'Register',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camps_wrangler'
    ],
    'node_theme_camps_edit_form' => [
      'mode' => 'edit',
      'title' => 'Updating: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camps_wrangler'
    ],
    'node_theme_camps_form_1_form' => [
      'mode' => 'form_1',
      'title' => 'Registering: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camps_wrangler'
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
 * Generate a form title based on the collective, registration and type.
 */
function _get_title($defaults, $collective, $registration){

  $collective_title = ' <div class="subtitle">' . $defaults['type'] . ' by the ' . $collective->getTitle() . ' collective</div>';

  return t(
    $defaults['title'],
    ['%title' => $registration ? $registration->getTitle() : '']
  ) . $collective_title;
}