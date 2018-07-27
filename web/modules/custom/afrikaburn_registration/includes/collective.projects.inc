<?php
/**
 * @file: collective-projects.inc
 * Contains collective project view preprocessors.
 */


/**
 * Adds form states to collective projects view.
 */
function ar_prepare_collective_projects(&$variables){

  $view = $variables['view'];

  $view->group_admin = Drupal\afrikaburn_collective\Access\CollectiveAdminCheck::isAdmin(
    \Drupal::currentUser()->id(),
    node_load($view->argument['field_collective_target_id']->value[0])
  );

  module_load_include('inc', 'afrikaburn_registration', 'includes/form');

  $settings = \Drupal::config('afrikaburn_registration.settings');

  foreach(_project_form_modes() as $key=>$map){

    $modes = $map['modes'];

    foreach($modes as $mode=>$mode_title){

      $state = array_keys(array_filter($settings->get($key . '/' . $mode)));
      $state = array_combine($state, $state);

      $variables['view']->form_modes[$key][$mode] = [
        'title' => $mode_title,
        'name' => $mode,
        'state' => $state,
      ];
    }
  }
}

/**
 * Constructs project links.
 */
function ar_prepare_project_links(&$variables) {

  $view = $variables['view'];
  $id = $view->id();
  $display = $view->current_display;

  if (in_array($display, ['current_block', 'past_block'])){

    $nid = $variables['row']->nid;
    $registration = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
    $type = $registration->get('type')->getValue()[0]['target_id'];
    $completed = array_column($registration->get('field_prjr_complete')->getValue(), 'value');
    $completed = array_combine($completed, $completed);
    $published = $registration->get('status')->getValue()[0]['value'];
    $links = false;

    if ($display == 'current_block'){

      foreach($view->form_modes[$type] as $mode){
        if (@$mode['state']['visible']) {

          $editable = @$mode['state']['editable'] && $view->group_admin;
          $complete = @$completed[$mode['name']];

          switch (true){
            case $editable && !$complete:
              $status = 'warning';
              $description = 'TODO: Needs completion.';
            break;
            case $editable && $complete:
              $status = 'status';
              $description = 'DONE: Still editable.';
            break;
            default:
              $status = 'lock';
              $description = 'LOCKED: Read only.';
          }

          $link = (
            $editable
              ? '<a href="/node/' . $nid . '/edit/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
              : '<a href="/node/' . $nid . '/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
          );

          $links[] = '<li class="messages messages--' . $status . '">' . $link . '</li>';
        }
      }

      $collective = \Drupal::routeMatch()->getParameter('node');
      $links[] = '<li class="messages messages--error"><a href="/node/' . $nid . '/delete?destination=/node/' . $collective->id() . '" title="Delete this registration. This cannot be undone!">Delete registration</a></li>';

    } else {

      foreach($view->form_modes[$type] as $mode){
        $links[] = '<li class="messages messages--archive"><a href="/node/' . $nid . '/' . $mode['name'] . '" target="_blank" title="ARCHIVED: Read only.">' . $mode['title'] . '</a></li>';
      }

      if (@$view->form_modes[$type]['form_1']['state']['open']){
        $links[] = '<li class="messages messages--recycle"><a href="/registration/' . $nid . '/reuse" target="_blank" title="Copy the information in this registration into a new submission.">Reuse this registration</a></li>';
      }
    }

    $variables['draft'] = $published ? '' : '<span>draft</span>';
    $variables['links'] = $links;
  }

}