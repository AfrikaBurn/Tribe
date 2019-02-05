<?php
/**
 * @file: project-wizard.inc
 * Contains wizard preprocessing for project registration
 */


/**
 * Implements hook_field_group_pre_render().
 * Sets the js-wizard classes on a form.
 * Hides administrative fieldset from muggles.
 */
function afrikaburn_registration_field_group_pre_render(&$element, &$group, &$form) {
  if ($group->group_name == 'group_project_registration' && $form['#wizard']){
    $form['#attributes']['class'][] = 'js-wizard';
  }
  if ($group->group_name == 'group_administrative' && !isset($form['#administrator'])){
    $form['group_administrative']['#attributes']['class'][] = 'hidden';
  }
}
