<?php
/**
 * @file: collective-projects.inc
 * Contains collective project view preprocessors.
 */

use Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\user\Entity\User;
use \Drupal\node\Entity\Node;

/**
 * Adds form states to collective projects view.
 */
function ar_prepare_collective_projects(&$variables){

  $view = $variables['view'];

  $view->group_admin = CollectiveController::isAdmin(
    Node::load($view->argument['field_collective_target_id']->value[0]),
    User::load(\Drupal::currentUser()->id())
  );

  module_load_include('inc', 'afrikaburn_registration', 'includes/form');

  $settings = \Drupal::config('afrikaburn_registration.settings');

  foreach(_project_form_modes() as $key=>$map){

    $modes = $map['modes'];

    foreach($modes as $mode=>$mode_title){

      $state = @array_keys(array_filter($settings->get($key . '/' . $mode)));
      $state = @array_combine($state, $state);

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
 * DO NOT FUCKING JUDGE. Drupal makes hacks of all...
 */
function ar_prepare_project_links(&$variables) {

  $view = $variables['view'];
  $id = $view->id();
  $display = $view->current_display;

  if (in_array($display, ['current', 'past'])){

    $nid = $variables['row']->nid;
    $registration = Node::load($nid);
    $type = $registration->get('type')->getValue()[0]['target_id'];
    $completed = array_column($registration->get('field_prjr_complete')->getValue(), 'value');
    $completed = array_combine($completed, $completed);
    $published = $registration->get('status')->getValue()[0]['value'];
    $links = false;

    if ($display == 'current'){

      foreach($view->form_modes[$type] as $mode){
        if (@$mode['state']['visible']) {

          $editable = @$mode['state']['editable'] && $view->group_admin;
          $complete = @$completed[$mode['name']];
          $pid = false;
          $link = false;

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

          switch($mode['name']){

            case 'support_camp':
              $query = \Drupal::entityQuery('node')
                ->condition('type', 'theme_camps')
                ->condition('field_supporting_project', $nid);
              $pid = @array_shift($query->execute());
              $cid = $view->args[0];

              $link =  $registration->get('field_allow_support_camp')->value
                ? (
                  $pid
                    ? ($editable
                        ? '<a href="/node/' . $pid . '/edit/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
                        : '<a href="/node/' . $pid . '/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
                      )
                    : ($editable
                        ? '<a href="/node/add/theme_camps/support_camp?field_collective='. $cid .'&field_supporting_project=' . $nid . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
                        : false
                      )
                  )
                : false;
            break;

            case 'waps':

              if ($registration->field_waps_allowed->value){
                $link = (
                  $editable
                    ? '<a href="/node/' . $nid . '/edit/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
                    : '<a href="/node/' . $nid . '/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
                );
              }

            break;

            default:

              $link = (
                $editable
                  ? '<a href="/node/' . $nid . '/edit/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
                  : '<a href="/node/' . $nid . '/' . $mode['name'] . '" target="_blank" title="'. $description .'">' . $mode['title'] . '</a>'
              );
          }

          if ($link) {
            $links[] = '<li class="messages messages--' . $status . '">' . $link . '</li>';
          }
        }
      }


      $collective = $registration->field_collective->entity;

      if ($collective && $view->group_admin){
        $links[] = '<li class="delete messages messages--error"><a href="/node/' . $nid . '/delete?destination=/node/' . $collective->id() . '" title="Delete this registration. This cannot be undone!">Delete registration</a></li>';
      }

    } else {

      foreach($view->form_modes[$type] as $mode){
        if (@$mode['state']['visible']) {
          $links[] = '<li class="messages messages--archive"><a href="/node/' . $nid . '/' . $mode['name'] . '" target="_blank" title="ARCHIVED: Read only.">' . $mode['title'] . '</a></li>';
        }
      }

      if (@$view->form_modes[$type]['form_1']['state']['open'] && $view->group_admin){
        $links[] = '<li class="messages messages--recycle"><a href="/registration/' . $nid . '/reuse" target="_blank" title="Copy the information in this registration into a new submission.">Reuse this registration</a></li>';
      }
    }

    $variables['draft'] = $published ? '' : '<span>draft</span>';
    $variables['links'] = $links;
  }

}


function ab_is_project_page(){

  module_load_include('inc', 'afrikaburn_registration', 'includes/form');

  $node = \Drupal::routeMatch()->getParameter('node');

  if ($node) {
    $bundle = $node->bundle();
    $modes = @_project_form_modes()[$bundle];
  }

  return isset($modes);
}