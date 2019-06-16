<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_collective\CollectiveController.
 */

namespace Drupal\afrikaburn_collective\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\afrikaburn_collective\Utils;
use \Drupal\Core\Cache\Cache;

class CollectiveController extends ControllerBase {


  /* --- Joining ---- */


  /**
   * Invite a participant to a collective
   * @param $collective Collective to join user to
   * @param $user       User to join
   */
  public static function join(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('member', $collective, $user);
    Utils::showStatus('@username now a member', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Invite a participant to a collective
   * @param $collective Collective to invite user to
   * @param $user       User to invite
   */
  public static function invite(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('invite', $collective, $user);
    Utils::showStatus('@username now invited', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Accept an invitation to a collective
   * @param $collective Collective invitation to accept
   * @param $user       Invited user
   */
  public static function accept(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('member', $collective, $user);
    CollectiveController::clear('invite', $collective, $user);
    Utils::showStatus('@username now a member', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Ignore an invitation to a collective
   * @param $collective Collective invitation to ignore
   * @param $user       Invited user
   */
  public static function ignore(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('invite', $collective, $user);
    Utils::showStatus('Invitation ignored', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal\Core\Url::fromUserInput(\Drupal::destination()->get()));
  }

  /**
   * Request to join a collective
   * @param $collective Collective to request membership to
   * @param $user       User to request membership against
   */
  public static function request(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('join', $collective, $user);
    Utils::showStatus('Membership requested', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Withdraw a request to join a collective
   * @param $collective Collective membership request to withdraw
   * @param $user       User that requested membership
   */
  public static function withdraw(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('join', $collective, $user);
    Utils::showStatus('Membership request revoked', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Approve a request to join a collective
   * @param $collective Collective to approve membership against
   * @param $user       User to approve membership against
   */
  public static function approve(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('join', $collective, $user);
    CollectiveController::set('member', $collective, $user);
    Utils::showStatus('@username now a member', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Reject a request to join a collective
   * @param $collective Collective to deny membership to
   * @param $user       User to deny membership against
   */
  public static function reject(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('join', $collective, $user);
    Utils::showStatus('Membership request rejected', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }


  /* --- Leaving --- */


  /**
   * Boot a participant from a collective
   * @param $collective Collective to boot user from
   * @param $user       User to boot
   */
  public static function leave(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('admin', $collective, $user);
    CollectiveController::clear('member', $collective, $user);
    Utils::showStatus('@username no longer a member', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('<front>'));
  }


  /* --- Managing --- */


  /**
   * Boot a participant from a collective
   * @param $collective Collective to boot user from
   * @param $user       User to boot
   */
  public static function boot(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::leave($collective, $user);
    Utils::showStatus('@username no longer a member', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Ban a participant from a collective
   * @param $collective Collective to ban user from
   * @param $user       User to ban
   */
  public static function ban(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('admin', $collective, $user);
    CollectiveController::clear('member', $collective, $user);
    CollectiveController::set('ban', $collective, $user);
    Utils::showStatus('@username now banned', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Promote a member to admin a collective
   * @param $collective Collective to promote user to admin in
   * @param $user       User to promote to admin
   */
  public static function admin(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('admin', $collective, $user);
    Utils::showStatus('@username now an Admin', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }

  /**
   * Strip a member from admining a collective
   * @param $collective Collective in which to strip user admin privileges
   * @param $user       User to strip admin privileges from
   */
  public static function strip(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('admin', $collective, $user);
    Utils::showStatus('@username no longer an Admin', Utils::currentUser(), $user);
    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $collective->id()]));
  }


  /* --- Role checking --- */


  /**
   * Checks whether a participant is invited to a collective
   * @param $collective Collective to check invitation against
   * @param $user       User to check invitation against
   */
  public static function isInvited($collective, $user){
    return CollectiveController::get('invite', $collective, $user);
  }

  /**
   * Checks whether a participant has requested to join to a collective
   * @param $collective Collective to check membership request against
   * @param $user       User to check membership request against
   */
  public static function isRequested($collective, $user){
    return CollectiveController::get('join', $collective, $user);
  }

  /**
   * Checks whether a participant is a member of a collective
   * @param $collective Collective to check membership against
   * @param $user       User to check membership against
   * @param $stealth    Ignore system admin status
   */
  public static function isMember($collective, $user, $stealth = FALSE){
    return
      !$stealth && (
        $user->hasRole('administrator') ||
        $user->hasRole('art_wrangler') ||
        $user->hasRole('mutant_vehicle_wrangler') ||
        $user->hasRole('theme_camp_wrangler') ||
        $user->hasRole('communications_wrangler') ||
        $user->hasRole('support_wrangler')
      ) || CollectiveController::get('member', $collective, $user);
  }

  /**
   * Checks whether a participant is an admin of a collective
   * @param $collective Collective to check administratorship against
   * @param $user       User to check administratorship against
   * @param $stealth    Ignore system admin status
   */
  public static function isAdmin($collective, $user){
    return
      !$stealth && (
        $user->hasRole('administrator') ||
        $user->hasRole('art_wrangler') ||
        $user->hasRole('mutant_vehicle_wrangler') ||
        $user->hasRole('theme_camp_wrangler') ||
        $user->hasRole('communications_wrangler') ||
        $user->hasRole('support_wrangler')
       ) || CollectiveController::get('admin', $collective, $user);
  }

  /**
   * Checks whether a participant is banned from a collective
   * @param $collective Collective to check banning against
   * @param $user       User to check banning against
   */
  public static function isBanned($collective, $user){
    return CollectiveController::get('ban', $collective, $user);
  }


  /* --- Projects checking --- */


  /**
   * Checks collective has current projects
   */
  public static function hasCurrentProjects(){

    $collective = Utils::currentCollective();

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
   */
  public static function hasPastProjects(){

    $collective = Utils::currentCollective();

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


  /* --- Settings checking --- */


  /**
   * Checks whether a collective has a setting
   * @param $collective Collective to check setting against
   * @param $setting    Setting to check
   */
  public static function setting($collective, $setting){
    return @array_fill_keys(
      array_column(
        $collective->field_settings->getValue(),
        'value'
      ),
      1
    )[$setting];
  }


  /* --- Flag Utility --- */


  /**
   * Sets a flag
   * @param $fid  Flag ID
   * @param $user User to flag for
   * @param $user Collective to flag
   */
  public static function set($flag_id, $collective, $user){
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById($flag_id);
    if (!$flag_service->getFlagging($flag, $collective, $user)){
      $flag_service->flag($flag, $collective, $user);
      Cache::invalidateTags($collective->getCacheTags());
    }
  }

  /**
   * Clears a flag
   * @param $fid  Flag ID
   * @param $user User to flag for
   * @param $user Collective to flag
   */
  public static function clear($flag_id, $collective, $user){
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById($flag_id);
    if ($flag_service->getFlagging($flag, $collective, $user)){
      $flag_service->unflag($flag, $collective, $user);
      Cache::invalidateTags($collective->getCacheTags());
    }
  }

  /**
   * Returns a flag
   * @param $fid  Flag ID
   * @param $user User to return flagging for
   * @param $user Collective to return flagging for
   */
  public static function get($flag_id, $collective, $user){
    $flag_service = \Drupal::service('flag');
    $flag = $flag_service->getFlagById($flag_id);
    return $flag_service->getFlagging($flag, $collective, $user);
  }


  /* --- Path utility --- */


  /**
   * Returns and loads path parameter entities
   * @return array an array containing the collective from the url.
   */
  public static function pathParams(){

    $cid = \Drupal::routeMatch()->getParameter('cid');
    $uid = \Drupal::routeMatch()->getParameter('uid');

    return [
      \Drupal::entityTypeManager()->getStorage('node')->load($cid),
      \Drupal\user\Entity\User::load($uid ? $uid : Utils::currentUser()->id())
    ];
  }
}