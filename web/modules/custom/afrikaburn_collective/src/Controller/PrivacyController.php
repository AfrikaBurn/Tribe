<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_collective\PrivacyController.
 */

namespace Drupal\afrikaburn_collective\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\afrikaburn_shared\Utils;
use \Drupal\Core\Cache\Cache;

class PrivacyController extends ControllerBase {

  /**
   * Check which fields a user may view of another
   */
  public static function maySee($user, $member){

    $uid = $user->id();
    $mid = $member->id();

    $admin_fields = db_query("
      SELECT
        disclosed.field_admins_value
      FROM {flagging__field_admins} disclosed
      LEFT JOIN {flagging} privacy
        ON privacy.id = disclosed.entity_id
      LEFT JOIN {flagging} administrator
        ON (administrator.entity_id = privacy.entity_id AND administrator.flag_id = 'admin')
      WHERE privacy.uid = $mid AND administrator.uid = $uid
    ")->fetchCol();
    $admin_fields = array_combine($admin_fields, $admin_fields);

    $member_fields = db_query("
      SELECT
        disclosed.field_members_value
      FROM {flagging__field_members} disclosed
      LEFT JOIN {flagging} privacy
        ON privacy.id = disclosed.entity_id
      LEFT JOIN {flagging} member
        ON (member.entity_id = privacy.entity_id AND member.flag_id = 'member')
      WHERE privacy.uid = $mid AND member.uid = $uid
    ")->fetchCol();
    $member_fields = array_combine($member_fields, $member_fields);

    return [
      'all' => array_merge($admin_fields, $member_fields),
      'admin' => $admin_fields,
      'member' => $member_fields,
    ];
  }
}