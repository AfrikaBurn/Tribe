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
        'field_prj_plc_binnekring',
        'field_final_placement',
        'field_plot',
        'field_neighbours',
        'field_ice_card',
        'field_placed',
        'field_placement_card',
        'field_placement_letter_sent',
      ],

    ]
  )[$id];
}

function _merge(){
  return [
    'administrative' => [
      'field_prj_adm_registration',
      'field_prj_snd_level',
      'field_prj_stc_size',
      'field_final_placement',
      'field_plot',
      'field_neighbours',
    ],
    'process' => [
      'field_prj_adm_wrangler',
      'field_prj_adm_review',
      'field_prjr_complete',
    ],
  ];
}

function _title($field){
  return @([
    'field_prj_brn_burning' => FALSE,
    'field_prj_cll_collaborate' => FALSE,
    'field_prjr_complete' => FALSE,
    'field_prj_snd_sound' => FALSE,
    'field_prj_plc_binnekring' => FALSE,
    'field_ice_card' => FALSE,
    'field_placed' => FALSE,
    'field_placement_card' => FALSE,
    'field_placement_letter_sent' => FALSE,
    'field_prj_snd_level' => 'Sound level',
  ])[$field];
}


/**
 * Prepares wrangler filter.
 */
function ar_prepare_wrangler_filter(&$form, $type){

  $view = \Drupal\views\Views::getView('wranglers');

  if (is_object($view)) {

    // ARGH! consistent naming so desperately needed!
    $type = [
      'art' => 'art_wrangler',
      'mutant_vehicles' => 'mutant_vehicle_wrangler',
      'performances' => 'art_wrangler',
      'theme_camps' => 'theme_camp_wrangler'
    ][$type];

    $view->setArguments([$type]);
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
    $form['completed']['#options']['All'] = t('- any form -');

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
      if ($view->field[$field]){
        $field_form = @array_shift(
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
        $field_form['#attributes']['class'][] = 'editor-parent';

        // Links to completed forms
        if ($field == 'field_prjr_complete'){
          foreach($field_form['#options'] as $option=>$markup){
            $field_form[$option]['#description'] =
              '<a target="_blank" href="/node/'.$nid.'/' . $option . '">view</a> ' .
              '<a target="_blank" href="/node/'.$nid.'/edit/' . $option . '">edit</a>';
            if ($option == 'grant')
            $field_form[$option]['#description'] .= '<a target="_blank" href="/node/'.$nid.'/' . $option . '_print">print</a>';
          }
        }

        $view->editor[$index][$field] = render($field_form);
      }
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

    // Move form
    foreach(_editable($view->id()) as $field){
      if ($view->field[$field]){
        $row['columns'][$field]['content'][0]['field_output'] = $view->editor[$index][$field];
      }
    }

    if ($view->current_display == 'bench'){
      // Merge admin columns
      foreach (_merge() as $column=>$fields) {
        foreach($fields as $field){
          if ($view->field[$field]){
            $row['columns'][$column]['content'][] = [
              'field_output' => @$row['columns'][$field]['content'][0]['field_output']
            ];
            unset($row['columns'][$field]);
            $row['columns'][$column]['attributes'] =  new \Drupal\Core\Template\Attribute(['class' => 'column-' . $column]);
          }
        }
      }

      // Latest comment
      $row['columns']['administrative']['content'][] = [
        'field_output' => @render(views_embed_view('wrangle_comments', 'latest', $nid))
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
      unset($form['content']['field_body']['widget'][0]['value']['#title']);
      $form['content']['#action'] .= '?destination=' . \Drupal\Component\Utility\UrlHelper::encodePath(drupal_get_destination()['destination']);
      $row['columns']['administrative']['content'][] = ['field_output' => render($form)];
      // Comment history
      $history = [
        '#type' => 'details',
        '#title' => 'History',
        'content' => views_embed_view('wrangle_comments', 'history', $nid),
      ];
      $row['columns']['administrative']['content'][] = [
        'field_output' => render($history)
      ];

      // Unset admin column headers
      foreach (_merge() as $index=>$fields) {
        foreach($fields as $field){
          unset($variables['header'][$field]);
        }
      }

      // Set merged column headings
      foreach (_merge() as $column=>$field) {
        $label = $column;
        $label[0] = strtoupper($label[0]);
        $variables['header'][$column] = [
          'content' => t($label),
          'title' => t($label),
          'attributes' => new \Drupal\Core\Template\Attribute(['class' => 'column-' . $column]),
        ];
      }
    }
  }

  // Prepare email addresses
  foreach($view->mails as $type=>$mails){
    $variables['mails'][$type] = implode(', ', array_combine($mails, $mails));
  }
}