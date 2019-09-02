<?php
/**
 * @file: form.inc
 */

use \Drupal\user\Entity\User;

 /**
  * Determine which forms have which modes
  */
function _project_form_modes(){
  return [

    'art' => [
      'title' => 'Artwork',
      'modes' => [
        'form_1' => 'Introduction',
        'form_2' => 'Specification',
        'form_3' => 'Publishing',
        'grant' => 'Grant application',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'waps' => 'Work Access Passes',
      ],
    ],

    'performances' => [
      'title' => 'Binnekring event',
      'modes' => [
        'form_1' => 'Registration',
        'grant' => 'Grant application',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'waps' => 'Work Access Passes',
      ],
    ],

    'mutant_vehicles' => [
      'title' => 'Mutant Vehicle',
      'modes' => [
        'form_1' => 'Registration',
        'grant' => 'Grant application',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'waps' => 'Work Access Passes',
      ],
    ],

    'theme_camps' => [
      'title' => 'Theme Camp',
      'modes' => [
        'form_1' => 'Registration',
        'form_2' => 'Specification',
        'form_3' => 'Publishing',
        'support_camp' => 'Support camp',
        'supplier' => 'Supplier registration',
        'waps' => 'Work Access Passes',
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
      'title' => 'Introduction: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_form_2_form' => [
      'mode' => 'form_2',
      'title' => 'Specification: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_form_3_form' => [
      'mode' => 'form_3',
      'title' => 'Publishing: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_grant_form' => [
      'mode' => 'grant',
      'title' => 'Grant application for: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    'node_art_waps_form' => [
      'mode' => 'waps',
      'title' => 'Work Access Passes for: %title',
      'type' => 'an Artwork',
      'wrangler' => 'art_wrangler'
    ],
    //TODO: Suppliers

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
      'title' => 'Introduction: %title',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    'node_performances_grant_form' => [
      'mode' => 'grant',
      'title' => 'Grant application for: %title',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    'node_performances_waps_form' => [
      'mode' => 'waps',
      'title' => 'Work Access Passes for: %title',
      'type' => 'a Binnekring event',
      'wrangler' => 'art_wrangler'
    ],
    //TODO: Suppliers

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
      'title' => 'Introduction: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    'node_mutant_vehicles_grant_form' => [
      'mode' => 'grant',
      'title' => 'Grant application for: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    'node_mutant_vehicles_waps_form' => [
      'mode' => 'waps',
      'title' => 'Work Access Passes for: %title',
      'type' => 'a Mutant Vehicle',
      'wrangler' => 'mutant_vehicle_wrangler'
    ],
    //TODO: Suppliers

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
      'title' => 'Introduction: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_form_2_form' => [
      'mode' => 'form_2',
      'title' => 'Specification: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_form_3_form' => [
      'mode' => 'form_3',
      'title' => 'Publishing: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_support_camp_form' => [
      'mode' => 'support_camp',
      'title' => 'Introduction: %title',
      'type' => 'a Support Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
    'node_theme_camps_waps_form' => [
      'mode' => 'waps',
      'title' => 'Work Access Passes for: %title',
      'type' => 'a Theme Camp',
      'wrangler' => 'theme_camp_wrangler'
    ],
  ];
}


/* ----- Form utilities ----- */


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
      return $cid
      ? \Drupal::entityTypeManager()->getStorage('node')->load($cid)
      : false;
  }

/**
 * Retrieve the collective from the URL or the registration.
 */
function _get_supporting_project($registration){
  $rid = \Drupal::request()->query->get('field_supporting_project')
    ? \Drupal::request()->query->get('field_supporting_project')
    : ($registration && $registration->field_supporting_project
      ? $registration->field_supporting_project->getValue()[0]['target_id']
      : false);
  return $rid
    ? \Drupal::entityTypeManager()->getStorage('node')->load($rid)
    : false;
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

/**
 * Determine the parameters of registration form altering
 */
function _get_registration_params($form_state, $defaults){
  $entity = $form_state->getFormObject()->getEntity();
  $user = User::load(\Drupal::currentUser()->id());
  $registration = array_filter(
    [
      $entity,
      \Drupal::routeMatch()->getParameter('node'),
    ]
  )[0];

  return [
    $entity,
    $user,
    $registration,
    _get_collective($registration), // $collective,
    _get_supporting_project($registration), // $supporting_project,
    $user->hasRole($defaults['wrangler']), // $is_wrangler,
    $user->hasRole('administrator'), // $is_admin,
    $entity->bundle(), // $bundle,
    @array_pop(explode('/', \Drupal::service('path.current')->getPath())), // $mode,
  ];
}

/**
 * Sets form title, Submit title, Submit handlers
 */
function _set_form_attrs(&$form, $defaults, $collective, $registration, $mode){
  $form['#title'] = [
    '#type' => 'markup',
    '#markup' => _get_title($defaults, $collective, $registration),
  ];
  $form['actions']['submit']['#value'] = 'Submit';
  array_unshift($form['actions']['submit']['#submit'], 'afrikaburn_registration_publish');
  $form['actions']['submit']['#submit'][] = 'afrikaburn_registration_redirect';

  if ($mode == 'support_camp'){
    $form['actions']['submit']['#submit'][] = 'afrikaburn_registration_support';
  }
}

/**
 * Set form default values
 */
function _set_form_defaults(&$form, $collective, $supporting_project, $defaults, $is_admin, $is_wrangler){
  $form['field_collective']['widget'][0]['target_id']['#default_value'] = $collective;
  $form['field_supporting_project']['widget'][0]['target_id']['#default_value'] = $supporting_project;
  $form['field_form_mode']['widget'][0]['value']['#default_value'] = $defaults['mode'];
  $form['field_prjr_complete']['widget']['#default_value'][] = $defaults['mode'];
  $form['#administrator'] = $is_admin || $is_wrangler;
}

/**
 * Hide fields and inappropriate complete options per type
 */
function _set_form_field_state(&$form, $is_admin, $is_wrangler, $bundle){
  $form['revision_log']['#access'] = $is_admin;
  $form['field_waps']['#access'] = $is_admin;
  $form['field_waps_allowed']['#access'] = $is_admin || $is_wrangler;
  $form['field_form_mode']['#type'] = 'hidden';
  $form['field_collective']['#access'] = $is_admin;
  $form['field_prjr_complete']['widget']['#options'] = array_intersect_key(
    $form['field_prjr_complete']['widget']['#options'],
    _project_form_modes()[$bundle]['modes']
  );
  if (!($is_wrangler || $is_admin)) $form['field_prjr_complete']['widget']['#type'] = 'value';
}

/**
 * Wizard or tabs (cake or death?)
 */
function _set_form_wizard_state(&$form, $form_state, $defaults, $supporting_project, $registration, $is_wrangler){
  $incomplete = array_search(
    $defaults['mode'],
    $defaults['mode'] == 'support_camp'
      ? array_column($supporting_project->field_prjr_complete->getValue(), 'value')
      : array_column($registration->field_prjr_complete->getValue(), 'value')
  ) === FALSE;

  $existing = isset($registration->nid->value);
  $errors = count($form_state->getErrors()) > 0;
  $form['#wizard'] = !$is_wrangler && (!$existing || $incomplete) || $errors;
}

/**
 * Prevent link spoofing
 */
function _verify_link(&$form, $collective, $mode, $supporting_project){
  if (!$collective || ($mode == 'support_camp' && !$supporting_project)){

    drupal_set_message('Oops! You really should do that using the links found on a collective page.', 'warning', true);

    $form['#access'] = FALSE;
    $form['#title'] = [
      '#type' => 'markup',
      '#markup' => '<a href="/" class="button">Ok</a>',
    ];

    return false;
  }

  return true;
}

/**
 * Generate the WAP form
 */
function _wap_form(&$form, $collective, $registration, $mode){
  if ($mode == 'waps'){

    $form['intro'] = [
      '#type' => 'markup',
      '#markup' =>
        '<div class="messages messages--warning">' .
        '* If a member of your collective does not appear in the dropdown, they have likely been assigned one elsewhere already.<br />' .
        '* Once assigned, WAPs can only be transfered through Quicket.<br />' .
        '* You do not have to assign all WAPs in one go, you can assign WAPs until April 15th.' .
        '</div>',
    ];

    $allowed = (int)$registration->field_waps_allowed->value;
    $assigned = count($registration->field_waps);
    $uids = \Drupal::database()->query("
      SELECT
        members.uid as uid
      FROM
        {flagging} AS members
      LEFT JOIN
        {paragraph__field_team_member} AS wap_member ON members.uid = wap_member.field_team_member_target_id
      LEFT JOIN
        {paragraphs_item_field_data} as wap ON wap.id = wap_member.entity_id
      LEFT JOIN
        {flagging} AS archived ON archived.entity_id = wap.parent_id AND archived.flag_id = 'archived'
      LEFT JOIN
        {flagging} AS banned ON banned.entity_id = members.uid AND banned.flag_id = 'wap_ban'
      WHERE
        members.flag_id = 'member' AND
        members.entity_id = :cid AND
        (
          (archived.flag_id = 'archived' AND wap.parent_id IS NOT NULL) OR
          (archived.flag_id IS NULL AND wap.parent_id IS NULL)
        ) AND banned.flag_id IS NULL",
      [':cid' => $collective->nid->value]
    )->fetchCol();

    $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($uids);
    $options = ['-- Unassigned --'];
    foreach($users as $user){
      $options[$user->uid->value] = $user->field_first_name->value . ' "' . $user->name->value . '" ' . $user->field_last_name->value;
    }

    $form['waps'] = [
      '#prefix' => '<table id="wap-assign"><thead><th>Member</th><th>Estimated arrival date</th></tr></thead><tbody>',
      '#type' => 'fieldset',
      '#suffix' => '</tbody></table>',
      '#tree' => TRUE,
    ];

    unset($form['actions']['delete']);
    if ($assigned == $allowed){
      unset($form['actions']);
    }

    foreach($registration->field_waps as $i=>$wap){
      $form['waps']['assigned_' . $i] = [
        '#prefix' => '<tr>',
        '#type' => 'markup',
        '#markup' =>
          '<td>' .
            $wap->entity->field_team_member->entity->field_first_name->value . ' "' .
            $wap->entity->field_team_member->entity->name->value . '" ' .
            $wap->entity->field_team_member->entity->field_last_name->value .
          '</td><td>' .
            ($wap->entity->field_estimated_date_of_arrival->date
              ? $wap->entity->field_estimated_date_of_arrival->date->format('d/m/Y')
              : 'UNSPECIFIED'
            ) .
      '</td>',
        '#suffix' => '</tr>',
      ];
    }

    if ($allowed - $assigned > 0){
      for ($i=0; $i<$allowed - $assigned; $i++){
        $form['waps'][$i] = [
          '#prefix' => '<tr>',
          '#type' => 'container',
          '#suffix' => '</tr>',
        ];
        $form['waps'][$i]['member'] = [
          '#prefix' => '<td>',
          '#type' => 'select',
          '#options' => $options,
          '#suffix' => '</td>',
        ];
        $form['waps'][$i]['arrives'] = [
          '#prefix' => '<td>',
          '#type' => 'date',
          '#suffix' => '</td>',
        ];
      }
    }

    $form['actions']['submit']['#submit'][] = 'afrikaburn_registration_wap_submit';
    $form['#validate'][] = 'afrikaburn_registration_wap_validate';
    $form['#validate'][] = 'afrikaburn_registration_wap_quicket';
    $form['#attached']['library'][] = 'afrikaburn_registration/registration';
  }
}