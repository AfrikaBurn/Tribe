<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */


function _editable($id){
  return (
    [

      'wrangle_art' => [
        'field_prj_adm_wrangler',
        'field_prj_brn_burning',
        'field_prj_cll_collaborate',
        'field_prjr_complete',
        'field_prj_adm_review',
        'field_prj_snd_level',
        'field_prj_snd_sound',
        'field_prj_stc_size',
      ],

      'wrangle_binnekring_events' => [
        'field_prj_adm_wrangler',
        'field_prj_cll_collaborate',
        'field_prjr_complete',
        'field_prj_adm_review',
        'field_prj_snd_level',
      ],

      'wrangle_mutant_vehicles' => [
        'field_prj_adm_wrangler',
        'field_prj_cll_collaborate',
        'field_prjr_complete',
        'field_prj_adm_review',
        'field_prj_adm_registration',
        'field_prj_snd_sound',
        'field_prj_snd_level',
      ],

      'wrangle_theme_camps' => [
        'field_prj_adm_wrangler',
        'field_prj_cll_collaborate',
        'field_prjr_complete',
        'field_prj_adm_review',
        'field_prj_snd_sound',
        'field_prj_snd_level',
      ],

    ]
  )[$id];
}

function _merge(){
  return [
    'field_prj_adm_wrangler',
    'field_prj_adm_review',
    'field_prjr_complete',
    'field_prj_adm_registration',
    'field_prj_snd_level',
    'field_prj_stc_size',
  ];
}

function _title($field){
  return ([
    'field_prj_brn_burning' => FALSE,
    'field_prj_cll_collaborate' => FALSE,
    'field_prjr_complete' => FALSE,
    'field_prj_snd_sound' => FALSE,
    'field_prj_snd_level' => 'Sound level',
  ])[$field];
}


/**
 * Prepares wrangler filter.
 */
function ar_prepare_wrangler_filter(&$form, $type){

  $view = \Drupal\views\Views::getView('wranglers');

  if (is_object($view)) {

    $view->setArguments([$type . '_wrangler']);
    $view->setDisplay('wrangler_select');
    $view->preExecute();
    $view->execute();

    $form['wrangler']['#type'] = 'select';
    $form['wrangler']['#options'][''] = t('- any wrangler -');
    $form['wrangler']['#options']['unassigned'] = t('- UNASSIGNED -');
    foreach($view->result as $row){
      $form['wrangler']['#options'][$row->uid] =
        $row->_entity->get('field_first_name')->value .
        ' "' . $row->_entity->get('name')->value . '" ' .
        $row->_entity->get('field_last_name')->value;
    }

    $form['size']['#options']['All'] = t('- any size -');
    $form['status']['#options']['All'] = t('- any state -');
    $form['location']['#options']['All'] = t('- anywhere -');

  }
}

/**
 * Prepares wrangle view fields
 */
function ab_prepare_wrangler_view(&$variables){

  $view = $variables['view'];
  $name = $view->id();
  $type = str_replace('wrangle_', '', $name);
  $rows = $variables['rows'];
  $mails = [];

  foreach($rows[0]['#rows'] as $index=>&$row){

    $nid = $row->_entity->id();
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $form = \Drupal::service('entity.form_builder')->getForm($node);

    $mails[] = [
      'contact' => $row->_relationship_entities['field_prj_gen_contact']
        ? $row->_relationship_entities['field_prj_gen_contact']->get('mail')->value
        : FALSE,
      'lead' => $row->_relationship_entities['field_prj_gen_lead']
        ? $row->_relationship_entities['field_prj_gen_lead']->get('mail')->value
        : FALSE,
      'manager' => $row->_relationship_entities['field_prj_gen_manager']
        ? $row->_relationship_entities['field_prj_gen_manager']->get('mail')->value
        : FALSE,
    ];

    foreach(_editable($view->id()) as $field){

      $field_form = array_shift(
        array_filter(
          [
            $form[$field]['value'],
            $form[$field]['widget']['value'],
            $form[$field]['widget'],
          ]
        )
      );
      // Title
      $title = _title($field);
      if (isset($title)) {
        if ($title) $field_form['#title'] = $title;
        else unset($field_form['#title']);
      }

      unset($field_form['#description']);
      $field_form['#attributes']['autocomplete'] = "off";

      // Links to completed forms
      if ($field == 'field_prjr_complete'){
        foreach($field_form['#options'] as $option=>$markup){
          $field_form[$option]['#description'] =
            '<a target="_blank" href="/node/'.$nid.'/' . $option . '">view</a> ' .
            '<a target="_blank" href="/node/'.$nid.'/edit/' . $option . '">edit</a>';
        }
      }

      $view->editor[$index][$field] = render($field_form);
    }
  }

  $view->mails = [
    'contact' => array_column($mails, 'contact'),
    'lead' => array_column($mails, 'lead'),
    'manager' => array_column($mails, 'manager'),
  ];
}

/**
 * Inserts wrangle view fields
 */
function ab_insert_wrangler_view_rows(&$variables){

  $view = $variables['view'];

  // Insert forms
  foreach($variables['rows'] as $index=>&$row){

    $nid = $view->result[$index]->_entity->id();
    $row['nid'] = $nid;

    foreach(_editable($view->id()) as $field){
      $row['columns'][$field]['content'][0]['field_output'] = $view->editor[$index][$field];
    }

    // Merge admin columns
    foreach (_merge() as $i=>$field) {
      $row['columns']['admin']['content'][] = [
        'field_output' => $row['columns'][$field]['content'][0]['field_output']
      ];
      unset($row['columns'][$field]);
    }

    // Latest comment
    $row['columns']['admin']['content'][] = [
      'field_output' => render(views_embed_view('wrangle_comments', 'latest', $nid))
    ];
    // Comment form
    $form = [
      '#type' => 'details',
      '#title' => 'Add comment',
      'content' => \Drupal::service('entity.form_builder')->getForm(
        \Drupal::entityTypeManager()->getStorage('comment')->create(
          [
            'entity_type'  => 'node',
            'entity_id'    => $nid,
            'field_name'   => 'field_admin_comments',
            'comment_type' => 'admin_comments',
            'pid' => NULL,
          ]
        )
      )
    ];
    unset($form['content']['field_body']['widget'][0]['#title']);
    $form['#action'] .= '?destination=' . \Drupal\Component\Utility\UrlHelper::encodePath(drupal_get_destination()['destination']);
    $row['columns']['admin']['content'][] = ['field_output' => render($form)];
    // Comment history
    $history = [
      '#type' => 'details',
      '#title' => 'History',
      'content' => views_embed_view('wrangle_comments', 'history', $nid),
    ];
    $row['columns']['admin']['content'][] = [
      'field_output' => render($history)
    ];
    $row['columns']['admin']['attributes'] =  new \Drupal\Core\Template\Attribute(['class' => 'column-admin']);
  }

  // Unset admin column headers
  foreach (_merge() as $index=>$field) {
    unset($variables['header'][$field]);
  }

  // Set merged admin column and comment heading
  $variables['header']['admin'] = [
    'content' => 'Administrative',
    'title' => t('Administrative'),
    'attributes' => new \Drupal\Core\Template\Attribute(['class' => 'column-admin']),
  ];

  // Prepare email addresses
  foreach($view->mails as $type=>$mails){
    $variables['mails'][$type] = implode(', ', array_combine($mails, $mails));
  }
}