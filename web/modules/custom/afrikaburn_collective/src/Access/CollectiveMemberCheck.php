<?php

/**
 * @file
 * Contains Collective Member access checking.
 */

namespace Drupal\afrikaburn_collective\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use \Drupal\user\Entity\User;

/**
 * Checks Collective Member access.
 */
class CollectiveMemberCheck implements AccessInterface {

  public function appliesTo() {
    return '_is_collective_member';
  }

  /**
   * Implements access().
   */
  public function access(AccountInterface $account) {

    static $user;
    $user = isset($user) ? $user : User::load(\Drupal::currentUser()->id());
    $node = \Drupal::routeMatch()->getParameter('nid');
    $bundle = $node ? $node->bundle() : FALSE;

    $roles = [
      'art' => 'art_wrangler',
      'performances' => 'art_wrangler',
      'mutant_vehicles' => 'mutant_vehicle_wrangler',
      'theme_camps' => 'theme_camp_wrangler',
    ];

    if ($node && in_array($bundle, array_keys($roles))){
      $field_collective = $node->get('field_collective');
      if ($field_collective) {
        $collective = $field_collective->first()
          ? $field_collective->first()->get('entity')->getTarget()
          : FALSE;
        return AccessResult::allowedIf($this::isMember($collective) || $user->hasRole('administrator') || $user->hasRole($roles[$bundle]));
      }
      return AccessResult::allowedIf($user->hasRole('administrator'));
    }

    if ($bundle == 'collective') {
      $settings = array_fill_keys(
        array_column($node->field_settings->getValue(), 'value'), 1
      );
      return AccessResult::allowedIf(
        $settings['public'] ||
        $user->hasRole('administrator') ||
        $this::isMember($node)
      );
    }

    return AccessResult::allowedIf(TRUE);
  }

  /**
   * Checks whether the current user is an admin
   */
  public static function isMember($collective){
    $uid = \Drupal::currentUser()->id();
    $admins = $collective
      ? $collective
        ->get('field_col_members')
        ->referencedEntities()
      : [];
    foreach ($admins as $admin) {
      if ($admin->id() == $uid){
        return TRUE;
      }
    }
    return FALSE;
  }
}