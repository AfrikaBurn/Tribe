<?php
/**
 * @file: collective-projects.inc
 * Contains wrangle project view preprocessors.
 */


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
  }
}
