<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_collective\CollectiveController.
 */

namespace Drupal\afrikaburn_collective\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;


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
  }

  /**
   * Ignore an invitation to a collective
   * @param $collective Collective invitation to ignore
   * @param $user       Invited user
   */
  public static function ignore(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('invite', $collective, $user);
  }

  /**
   * Request to join a collective
   * @param $collective Collective to request membership to
   * @param $user       User to request membership against
   */
  public static function request(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('join', $collective, $user);
  }

  /**
   * Withdraw a request to join a collective
   * @param $collective Collective membership request to withdraw
   * @param $user       User that requested membership
   */
  public static function withdraw(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('join', $collective, $user);
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
  }

  /**
   * Reject a request to join a collective
   * @param $collective Collective to deny membership to
   * @param $user       User to deny membership against
   */
  public static function reject(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('join', $collective, $user);
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
  }

  /**
   * Promote a member to admin a collective
   * @param $collective Collective to promote user to admin in
   * @param $user       User to promote to admin
   */
  public static function admin(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::set('admin', $collective, $user);
  }

  /**
   * Strip a member from admining a collective
   * @param $collective Collective in which to strip user admin privileges
   * @param $user       User to strip admin privileges from
   */
  public static function strip(){
    list($collective, $user) = CollectiveController::pathParams();
    CollectiveController::clear('admin', $collective, $user);
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
   */
  public static function isMember($collective, $user){
    return
      $user->hasRole('administrator') ||
      $user->hasRole('art_wrangler') ||
      $user->hasRole('mutant_vehicle_wrangler') ||
      $user->hasRole('theme_camp_wrangler') ||
      $user->hasRole('communications_wrangler') ||
      $user->hasRole('support_wrangler') ||
      CollectiveController::get('member', $collective, $user);
  }

  /**
   * Checks whether a participant is an admin of a collective
   * @param $collective Collective to check administratorship against
   * @param $user       User to check administratorship against
   */
  public static function isAdmin($collective, $user){
    return
      $user->hasRole('administrator') ||
      $user->hasRole('art_wrangler') ||
      $user->hasRole('mutant_vehicle_wrangler') ||
      $user->hasRole('theme_camp_wrangler') ||
      $user->hasRole('communications_wrangler') ||
      $user->hasRole('support_wrangler') ||
      CollectiveController::get('admin', $collective, $user);
  }

  /**
   * Checks whether a participant is banned from a collective
   * @param $collective Collective to check banning against
   * @param $user       User to check banning against
   */
  public static function isBanned($collective, $user){
    return CollectiveController::get('ban', $collective, $user);
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
    $flag = $flag_service->getFlagById($fid);
    if (!$flag_service->getFlagging($flag, $collective, $user)){
      $flag_service->flag($flag, $collective, $user);
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
    $flag = $flag_service->getFlagById($fid);
    if ($flag_service->getFlagging($flag, $collective, $user)){
      $flag_service->unflag($flag, $collective, $user);
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
      \Drupal\user\Entity\User::load($uid ? $uid : \Drupal::currentUser()->id())
    ];
  }


  /* --- Collective utility --- */


  /**
   * Returns a collective associated with the current path or project, if any.
   * @return object collective associated with the current path or false if none.
   */
  public static function currentCollective(){

    $cid = array_shift(
        array_filter(
          [
            \Drupal::routeMatch()->getParameter('cid'),
            \Drupal::routeMatch()->getParameter('node'),
          ]
        )
    );
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    return
      $node
      ? ($node->bundle() == 'collective'
        ? $node : ($node->get('field_collective')
            ? $node->get('field_collective')->value
            : false
          )
      ) : false;
  }
}