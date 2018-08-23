<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */

function _editable($id){
  return (
    [
      'wrangle_sanctuary' => [
        'webform_submission_value_10',
        'webform_submission_value_12',
      ],
    ]
    )[$id];
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
    $view_builder = \Drupal::service('entity_type.manager')->getViewBuilder('webform');
    $build = $view_builder->view($webform);
  }

  $view->webform = $webform;
}

/**
 * Inserts wrangle view fields
 */
function ab_insert_wrangler_view_rows(&$variables){
  foreach($variables['rows'] as $index=>&$row){
    // Move form
    foreach(_editable($view->id()) as $field){
      $row['columns'][$field]['content'][0]['field_output'] = $view->webform['elements'][$index][$field];
    }
  }
}