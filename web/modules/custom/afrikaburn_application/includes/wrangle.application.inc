<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */


function _editable($id){
  return (
    [
      'wrangle_subsidised' => [
        'wrangler' => 'webform_submission_value_4',
        'comment' => 'webform_submission_value_1',
        'review' => 'webform_submission_value_15',
      ],
      'wrangle_community_tna' => [
        'ticket_allocated' => 'webform_submission_value_16',
        'welfare_allocated' => 'webform_submission_value_20',
        'transport_allocated' => 'webform_submission_value_18',
      ],
    ]
  )[$id];
}

function _title($id){
  return (
    [
      'wrangle_subsidised' => [
        'status' => FALSE,
      ],
      'wrangle_dpw' => [
        'status' => FALSE,
      ],
      'wrangle_community_tna' => [
        'ticket_allocated' => false,
        'welfare_allocated' => false,
        'transport_allocated' => false,
      ],
    ]
  )[$id];
}

function _merge(){
  return [
    'wrangle_subsidised' => [
      'administrative' => [
        'webform_submission_value_4',
        'webform_submission_value_15',
        'webform_submission_value_1',
        'operations'
      ],
    ],
    'wrangle_community_tna' => [],
  ];
}


/**
 * Prepares wrangle view fields
 */
function _prepare_wrangler_view(&$variables){

  $view = $variables['view'];
  $name = $view->id();
  $form_id = array_shift($view->filter['webform_id']->value);

  static $webform;
  if (!$webform){
    $webform = \Drupal::entityTypeManager()->getStorage('webform')->load($form_id);
  }

  $view->webform = $webform;
}

/**
 * Inserts wrangle view fields
 */
function _insert_wrangler_view_rows(&$variables){

  $view = $variables['view'];
  $view_id = $view->id();

  foreach($variables['rows'] as $index=>&$row){

    $sid = $view->result[$index]->sid;
    $submission = \Drupal\webform\Entity\WebformSubmission::load($sid);
    $data = $submission->getData();
    $form = $view->webform->getSubmissionForm(['data' => $data]);
    $variables['rows'][$index]['sid'] = $view->webform->id() . '/' . $sid;

    // Set form
    foreach(_editable($view_id) as $field=>$alias){

      $field_form = $form['elements']['administrative'][$field];
      $title = isset(_title($view_id)[$field]) ? _title($view_id)[$field] : NULL;

      if ($title !== NULL){
        if ($title) {
          $field_form['#title'] = $title;
          $field_form['#admin_title'] = $title;
        } else {
          unset($field_form['#title']);
          unset($field_form['#admin_title']);
        }
      }

      $row['columns'][$alias]['content'][0]['field_output'] = render($field_form);
    }

    // Merge admin columns
    foreach (_merge()[$view_id] as $column=>$fields) {
      foreach($fields as $field){
        $row['columns'][$column]['content'][] = [
          'field_output' => @$row['columns'][$field]['content'][0]['field_output']
        ];
        unset($row['columns'][$field]);
        $row['columns'][$column]['attributes'] =  new \Drupal\Core\Template\Attribute(['class' => 'column-' . $column]);
      }
    }

    unset($row['columns']['sid']);
  }

  // Unset admin column headers
  foreach (_merge()[$view_id] as $index=>$fields) {
    foreach($fields as $field){
      unset($variables['header'][$field]);
    }
  }

  unset($variables['header']['sid']);

  // Set merged column headings
  foreach (_merge()[$view_id] as $column=>$field) {
    $label = $column;
    $label[0] = strtoupper($label[0]);
    $variables['header'][$column] = [
      'content' => t($label),
      'title' => t($label),
      'attributes' => new \Drupal\Core\Template\Attribute(['class' => 'column-' . $column]),
    ];
  }
}