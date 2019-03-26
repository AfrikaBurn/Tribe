<?php
/**
 * @file: util.inc
 * Contains collective utilities.
 */


use \Drupal\afrikaburn_collective\Access\CollectiveMemberCheck;
use \Drupal\afrikaburn_collective\Access\CollectiveAdminCheck;
use \Drupal\afrikaburn_collective\Controller\MemberController;


/**
 * Checks whether current user is a member or not
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_admin(){

  $user = \Drupal::currentUser();
  $collective = _collective();

  return $collective && (
    _found('administrator', $user->getRoles()) ||
    CollectiveAdminCheck::isAdmin($user->id(), $collective)
  );
}

/**
 * Checks whether current user is a member or not
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_member(){

  $user = \Drupal::currentUser();
  $collective = _collective();

  return $collective && (
    _found('administrator', $user->getRoles()) ||
    CollectiveMemberCheck::isMember($collective)
  );
}

/**
 * Checks collective has current projects
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_current_projects(){

  $collective = _collective();

  return $collective
    ? db_query("
        SELECT
          COUNT({node_field_data}.nid) as rows
        FROM
          {node_field_data}
          LEFT JOIN {flagging} ON {node_field_data}.nid = {flagging}.entity_id AND {flagging}.flag_id = 'archived',
          {node__field_collective}
        WHERE {node_field_data}.type IN ('art', 'theme_camps', 'mutant_vehicles', 'performances')
          AND {node__field_collective}.entity_id = {node_field_data}.nid
          AND {node__field_collective}.field_collective_target_id = ?
          AND {flagging}.entity_id IS NULL
        ", [$collective->id()]
      )->fetchCol('rows')[0]
    : 0;
}

/**
 * Checks collective has past projects
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_past_projects(){

  $collective = _collective();

  return $collective
    ? db_query("
        SELECT
          COUNT({node_field_data}.nid) as rows
        FROM
          {node_field_data}
          LEFT JOIN {flagging} ON {node_field_data}.nid = {flagging}.entity_id AND {flagging}.flag_id = 'archived',
          {node__field_collective}
        WHERE {node_field_data}.type IN ('art', 'theme_camps', 'mutant_vehicles', 'performances')
          AND {node__field_collective}.entity_id = {node_field_data}.nid
          AND {node__field_collective}.field_collective_target_id = ?
          AND {flagging}.entity_id IS NOT NULL
        ", [$collective->id()]
      )->fetchCol('rows')[0]
    : 0;
}

/**
 * Checks whether current user is a member or not
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_requested(){
  $collective = _collective();
  return $collective && MemberController::hasRequested($collective);
}

/**
 * Checks whether the current collective has a setting set.
 */
function afrikaburn_collective_setting($setting, $col = false){

  $col = is_numeric($col)
    ? \Drupal::entityTypeManager()->getStorage('node')->load($col)
    : $col;

  $collective = $col
    ? $col
    : _collective();

  return $collective
    ? array_fill_keys(
        array_column(
          $collective->field_settings->getValue(),
          'value'
        ),
        1
      )[$setting]
    : FALSE;
}


/* ----- Private utils ----- */


/**
 * Checks for a needle in a haystack
 */
function _found($needle, $haystack){
  return !in_array(
    array_search($needle, $haystack),
    [FALSE, NULL],
    TRUE
  );
}

/**
 * Checks for a collective
 */
function _collective(){

  static $collective;
  if (isset($collective)) return $collective;

  $node = \Drupal::routeMatch()->getParameter('node');
  $collective = $node
    ? $node->bundle() == 'collective'
      ? $node
      : $node->field_collective->entity
    : FALSE;

  return $collective;
}