<?php

/**
 * @file
 * Contains Collective Admin access checking.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use \Drupal\user\Entity\User;

/**
 * Checks Collective Admin access.
 */
class CollectiveAdminCheck implements AccessInterface {

  public function appliesTo() {
    return '_is_collective_admin';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $account) {

    static $user;
    $uid = \Drupal::currentUser()->id();
    $user = isset($user) ? $user : User::load($uid);
    $node = \Drupal::routeMatch()->getParameter('nid');
    $bundle = $node ? $node->bundle() : FALSE;

    $roles = [
      'art' => 'art_wrangler',
      'performances' => 'art_wrangler',
      'mutant_vehicles' => 'mutant_vehicle_wrangler',
      'theme_camps' => 'theme_camp_wrangler',
    ];

    if ($user->hasRole('administrator') || (isset($roles[$bundle]) && $user->hasRole($roles[$bundle]))){
      return AccessResult::allowed(TRUE);
    }

    if ($node && in_array($bundle, array_keys($roles))){
      $field_collective = $node->get('field_collective');
      if ($field_collective) {
        $collective = $field_collective->first()->get('entity')->getTarget();
        return AccessResult::allowedIf($this::isAdmin($uid, $collective));
      }
    }

    if ($bundle == 'collective') {
      return AccessResult::allowedIf($this::isAdmin($uid, $node));
    }

    if (($cid = \Drupal::routeMatch()->getParameter('cid')) && (\Drupal::routeMatch()->getParameter('uid'))){
      $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);
      return AccessResult::allowedIf($this::isAdmin($uid, $collective));
    }

    return AccessResult::allowedIf(TRUE);
  }

  /**
   * Checks whether the current user is an admin
   */
  public static function isAdmin($uid, $collective){
    $admins = $collective
      ->get('field_col_admins')
      ->referencedEntities();
    foreach ($admins as $admin) {
      if ($admin->id() == $uid){
        return TRUE;
      }
    }
    return FALSE;
  }

}
