<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */


function _editable($id){
  return (
    [
      'wrangle_sanctuary' => [
        'arriving' => 'webform_submission_value_15',
        'departing' => 'webform_submission_value_16',
        'rsvp' => 'webform_submission_value_17',
        'signed_up_shifts' => 'webform_submission_value_13',
        'shifts_completed' => 'webform_submission_value_1',
        'status' => 'webform_submission_value_12',
        'approved_roles' => 'webform_submission_value_10',
        'assign_wap' => 'webform_submission_value_14',
      ],

      'wrangle_dpw' => [
        'assigned' => 'webform_submission_value_25',
      ],
    ]
  )[$id];
}

function _title($id){
  return (
    [
      'wrangle_sanctuary' => [
        'status' => FALSE,
      ],
      'wrangle_dpw' => [
        'status' => FALSE,
      ],
    ]
  )[$id];
}

function _merge(){
  return [
    'wrangle_sanctuary' => [
      'administrative' => [
        'webform_submission_value_17',
        'webform_submission_value_10',
        'webform_submission_value_12',
        'webform_submission_value_14',
        'operations',
      ],
      'shifts' => [
        'webform_submission_value_15',
        'webform_submission_value_16',
        'webform_submission_value_13',
        'webform_submission_value_1',
      ],
    ],
    'wrangle_dpw' => [],
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
  $mails = [];

  foreach($variables['rows'] as $index=>&$row){

    $sid = $view->result[$index]->sid;
    $submission = \Drupal\webform\Entity\WebformSubmission::load($sid);
    $data = $submission->getData();
    $form = $view->webform->getSubmissionForm(['data' => $data]);
    $variables['rows'][$index]['sid'] = $view->webform->id() . '/' . $sid;
    $mails[] = $data['email'];

    // Open actions in new window
    $row['columns']['operations']['content'][0]['field_output']['#markup'] = str_replace(
      '<a href', '<a target="_blank" href',
      $row['columns']['operations']['content'][0]['field_output']['#markup']
    );

    // Set form
    foreach(_editable($view_id) as $field=>$alias){

      $field_form = array_shift(
        array_filter(
          [
            $form['elements'][$field],
            $form['elements']['admin'][$field],
            $form['elements']['administrative'][$field],
          ]
        )
      );

      $title = isset(_title($view_id)[$field]) ? _title($view_id)[$field] : NULL;

      if ($title != NULL){
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

  $variables['mails'] = implode(', ', $mails);
}