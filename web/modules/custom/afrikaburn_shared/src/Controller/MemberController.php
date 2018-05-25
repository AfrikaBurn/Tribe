<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_shared\MemberController.
 */

namespace Drupal\afrikaburn_shared\Controller;


use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class MemberController extends ControllerBase {

  /**
   * Invite a person to the group
   */
  public static function invite($cid = FALSE) {

    $emails = \Drupal::request()->request->get('emails');
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);
    $emails = explode(',', $emails);

    if ($collective->bundle() == 'collective'){
      self::addToInvites($emails, $collective);
    }
    $collective->save();

    drupal_set_message(
      t(
        'You have invited %emails to join %collective',
        [
            '%emails' => implode(', ', $emails),
            '%collective' => $collective->getTitle(),
        ]
      ),
      'status',
      TRUE
    );

    $redirect = new RedirectResponse('/node/' . $cid);
    $redirect->send();
    return [];
  }

  /**
   * Accept a group invitation
   */
  public static function accept($cid = FALSE) {

    $cid = \Drupal::routeMatch()->getParameter('cid');
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if ($collective && $collective->bundle() == 'collective'){

      $uid = \Drupal::currentUser()->id();
      $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
      $inviteIndex = self::inviteIndex($user, $collective);

      if (count($inviteIndex)) {

        self::addToMembers($user, $collective);
        self::removeFromInvites($inviteIndex, $collective);
        $collective->save();

      } else {
        return array(
          '#type' => 'markup',
          '#markup' => 'Oh no! This invitation is meant for a different email address! Make sure you get invited to collectives with this email address:<br />' . $user->getEmail(),
        );
      }

    } else {
      return array(
        '#type' => 'markup',
        '#markup' => t('Oh no! It seems that this group no longer exists!'),
      );
    }

    $redirect = new RedirectResponse('/node/' . $cid);
    $redirect->send();
    return [];
  }

  /**
   * Ignore a group invitation
   */
  public static function ignore($cid = FALSE) {

    $nid = \Drupal::routeMatch()->getParameter('nid');
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    if ($collective->bundle() == 'collective'){

      $uid = \Drupal::currentUser()->id();
      $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
      $inviteIndex = self::inviteIndex($user, $collective);

      if (count($inviteIndex)) {

        self::removeFromInvites($inviteIndex, $collective);
        $collective->save();

      } else {
        return array(
          '#type' => 'markup',
          '#markup' => 'Oh no! This invitation is meant for a different email address! Make sure you get invited to collectives with this email address:<br />' . $user->getEmail(),
        );
      }

    } else {
      return array(
        '#type' => 'markup',
        '#markup' => t('Oh well! It seems that group no longer exists anyway!'),
      );
    }

    $redirect = new RedirectResponse('/');
    $redirect->send();
    return [];
  }

  /**
   * Remove a member from a group
   */
  public static function boot($cid = FALSE, $uid = FALSE) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if($collective->getOwner()->id() != $uid){

      if (isset($user) && $collective->bundle() == 'collective'){

      $member_index = self::memberIndex($uid, $collective, 'field_col_members');
      $admin_index = self::memberIndex($uid, $collective, 'field_col_admins');

      // Make sure we delete only one instance
      $member_index = count ($member_index) ? [array_shift($member_index)] : [];
      $admin_index = count ($admin_index) ? [array_shift($admin_index)] : [];

        self::removeFromMembers($member_index, $collective);
        self::removeFromAdmins($admin_index, $collective);
        $collective->save();
      }

      drupal_set_message(
        t(
          '%user has been booted from %collective', 
          [
              '%user' => $user->getUsername(), 
              '%collective' => $collective->getTitle(),
          ]
        ),
        'status',
        TRUE
      );      
    } else {
      drupal_set_message(
        t('You cannot boot the owner of a collective!'),
        'status',
        TRUE
      );
    }

    $redirect = new RedirectResponse('/node/' . $cid);
    $redirect->send();
    return [];
  }

  /**
   * Promote member to admin
   */
  public static function admin($cid = FALSE, $uid = FALSE) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if (isset($user) && $collective->bundle() == 'collective' && !count(self::memberIndex($uid, $collective, 'field_col_admins'))){
      self::addToAdmins($user, $collective);
      $collective->save();
    }

    drupal_set_message(
      t(
        '%user is now an administrator of %collective', 
        [
            '%user' => $user->getUsername(), 
            '%collective' => $collective->getTitle(),
        ]
      ),
      'status',
      TRUE
    );

    $redirect = new RedirectResponse('/node/' . $cid);
    $redirect->send();
    return [];
  }

  /* ---- CRUD ---- */

  // Add email address to invites
  private static function addToInvites($emails, $collective) {
    foreach($emails as $email){
      if (!count(self::inviteIndex($email, $collective))){
        $collective->get('field_col_invitee')->appendItem(trim($email));
      }
    }
  }

  // Remove email address(es) from invites
  private static function removeFromInvites($inviteIndexes, $collective) {
    foreach(array_reverse($inviteIndexes) as $index){
      $collective->get('field_col_invitee')->removeItem($index);    
    }
  }

  // Add user to members
  private static function addToMembers($user, $collective) {
    if (!count(self::memberIndex($user->id(), $collective, 'field_col_members'))){
      $collective->get('field_col_members')->appendItem($user);
    }
  }

  // Remove user from members
  private static function removeFromMembers($memberIndexes, $collective) {
    if (count($memberIndexes)){
      $collective->get('field_col_members')->removeItem(array_reverse($memberIndexes)[0]);    
    }
  }

  // Add user to Admins
  private static function addToAdmins($user, $collective) {
    if (!count(self::memberIndex($user->id(), $collective, 'field_col_admins'))){
      $collective->get('field_col_admins')->appendItem($user);
    }
  }

  // Remove user from Admins
  private static function removeFromAdmins($memberIndexes, $collective) {
    if (count($memberIndexes)){
      $collective->get('field_col_admins')->removeItem(array_reverse($memberIndexes)[0]);    
    }
  }

  /* ---- Utility ---- */

  /**
   * Returns the index(es) of a users invite.
   */
  private static function inviteIndex($user, $collective) {

    $indexes = [];
    $field_col_invitee = $collective->get('field_col_invitee');

    if ($field_col_invitee){

      $invitees = array_column(
        $field_col_invitee->getValue(),
        'value'
      );

      $mails = is_string($user)
        ? [$user]
        : [$user->get('mail')->getValue(), $user->get('field_secondary_mail')->getValue()];
      foreach($mails as $index=>$mail){
        if ($mail) {
          $value = array_values(array_column($mail, 'value'))[0];
          if (in_array(strtolower($value), array_map('strtolower', $invitees))) {
            $indexes[$index] = $index;
          }
        }
      }
    }

    return $indexes;
  }

  /**
   * Returns the index(es) of a users invite.
   */
  private static function memberIndex($uid, $collective, $field) {

    $indexes = [];
    $field_col_members = $collective->get($field);

    if ($field_col_members){

      $members = array_column(
        $field_col_members->getValue(),
        'target_id'
      );

      $indexes = [];
      foreach($members as $index => $mid){
        if ($mid == $uid) {
          $indexes[$index] = $index;
        }
      }
    }

    return $indexes;
  }


}

