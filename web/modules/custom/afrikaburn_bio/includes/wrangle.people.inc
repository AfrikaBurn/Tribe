<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */


function _editable($id){
  return (
    [
      'wrangle_people' => [
        'mail',
        'field_secondary_mail',
        'field_mobile',
        'field_newsletter',
      ],

    ]
  )[$id];
}

function _merge(){
  return [
    'contact' => [
      'mail',
      'field_secondary_mail',
      'field_mobile',
    ],
  ];
}

function _title($field){
  return @([
    'mail' => FALSE,
    'field_secondary_mail' => FALSE,
    'field_mobile' => FALSE,
    'field_newsletter' => FALSE,
  ])[$field];
}


/**
 * Prepares wrangle view fields
 */
function _prepare_wrangler_view(&$variables){

  $view = $variables['view'];
  $name = $view->id();
  $type = str_replace('wrangle_', '', $name);
  $rows = $view->result;

  foreach($rows as $index=>&$row){

    $uid = $row->_entity->get('uid')->value;
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $form = \Drupal::service('entity.form_builder')->getForm($user);

    foreach(_editable($view->id()) as $field){

      $field_form = @array_shift(
        array_filter(
          [
            $form[$field]['widget'][0]['value'],
            $form[$field]['widget']['value'],
            $form[$field]['widget'],
            $form[$field]['value'],
            $form['account'][$field],
          ]
        )
      );
      // Title
      $title = _title($field);
      if (isset($title)) {
        if ($title) {
          $field_form['#title'] = $title;
        } else {
          $field_form['#attributes']['placeholder'] = $field_form['#title'];
          unset($field_form['#title']);
        }
      }

      unset($field_form['#required']);
      unset($field_form['#description']);
      unset($field_form['#description_display']);
      $field_form['#attributes']['autocomplete'] = "off";
      $field_form['#prefix'] = '<div class="editor-parent">';
      $field_form['#suffix'] = '</div>';

      $view->editor[$index][$field] = render($field_form);
    }
  }
}

/**
 * Inserts wrangle view fields
 */
function _insert_wrangler_view_rows(&$variables){

  $view = $variables['view'];

  // Insert forms
  foreach($variables['rows'] as $index=>&$row){

    $uid = $view->result[$index]->_entity->id();
    $row['uid'] = $uid;

    // Move form
    foreach(_editable($view->id()) as $field){
      $row['columns'][$field]['content'][0]['field_output'] = $view->editor[$index][$field];
    }

    // Merge admin columns
    foreach (_merge() as $column=>$fields) {

      $row['columns'] =
        array_slice($row['columns'], 0, 8, TRUE) +
        [$column => ['attributes' => new \Drupal\Core\Template\Attribute(['class' => 'column-' . $column])]] +
        array_slice($row['columns'], 8, NULL, TRUE);

      foreach($fields as $field){
        $row['columns'][$column]['content'][] = [
          'field_output' => @$row['columns'][$field]['content'][0]['field_output']
        ];
        unset($row['columns'][$field]);
      }
    }

    if ($uid > 1){
      $reset = ['#markup' => '<a class="button" href="/people/reset/' . $uid . '?destination=' . \Drupal\Component\Utility\UrlHelper::encodePath(drupal_get_destination()['destination']) . '">Send magic login link</a>'];
      $row['columns']['operations']['content'][] = ['field_output' => render($reset)];
    }
  }

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
    $variables['header'] =
      array_slice($variables['header'], 0, 5, TRUE) +
      [
        $column => [
          'content' => t($label),
          'title' => t($label),
          'attributes' => new \Drupal\Core\Template\Attribute(['class' => 'column-' . $column]),
        ]
      ] +
      array_slice($variables['header'], 5, NULL, TRUE);
  }
}

/**
 * Prepares wrangler filter.
 */
function _prepare_wrangler_filter(&$form, $type){
  $form['user']['#placeholder'] = 'Name or email';
  $form['field_id_number_value']['#placeholder'] = 'SA ID or Passport Number';
  $form['status']['#options']['All'] = t('- any status -');
}