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
  $collective = \Drupal::routeMatch()->getParameter('node');
  return
    _found('administrator', $user->getRoles()) ||
    CollectiveAdminCheck::isAdmin($user->uid, $collective);
}

/**
 * Checks whether current user is a member or not
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_member(){
  $user = \Drupal::currentUser();
  $collective = \Drupal::routeMatch()->getParameter('node');
  return
    _found('administrator', $user->getRoles()) ||
    CollectiveMemberCheck::isMember($collective);
}

/**
 * Checks whether current user is a member or not
 * Used by Collective right hand blocks
 */
function afrikaburn_collective_requested(){
  $collective = \Drupal::routeMatch()->getParameter('node');
  return MemberController::hasRequested($collective);
}

/**
 * Checks whether the current collective has a setting set.
 */
function afrikaburn_collective_setting($setting, $col = false){

  $col = is_numeric($col)
    ? \Drupal::entityTypeManager()->getStorage('node')->load($col)
    : $col;

  if (!$col){
    static $collective = false;
    if (!$collective) $collective = \Drupal::routeMatch()->getParameter('node');
  }

  if (!$col || $collective) return;

  static $settings = false;
  if (!$settings) $settings = array_fill_keys(
    array_column(
      ($col ? $col : $collective)->field_settings->getValue(),
      'value'
    ),
    1
  );

  return $settings[$setting];
}

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