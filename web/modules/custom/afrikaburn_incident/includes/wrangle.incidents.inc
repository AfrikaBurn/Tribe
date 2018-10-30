<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */


function _editable($id){
  return (
    [

      'wrangle_incidents' => [
        'field_incident_status',
      ],

    ]
  )[$id];
}

/**
 * Prepares wrangle view fields
 */
function ab_prepare_incident_view(&$variables){

  $view = $variables['view'];
  $name = $view->id();
  $type = str_replace('wrangle_', '', $name);
  $rows = $variables['rows'];
  $mails = [];

  foreach($rows[0]['#rows'] as $index=>&$row){

    $nid = $row->_entity->id();
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $form = \Drupal::service('entity.form_builder')->getForm($node);

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

        unset($field_form['#description']);
        unset($field_form['#title']);
        $field_form['#attributes']['autocomplete'] = "off";
        $field_form['#attributes']['class'][] = 'editor-parent';

        $view->editor[$index][$field] = render($field_form);
      }
    }
  }
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