<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_collective\MemberController.
 */

namespace Drupal\afrikaburn_collective\Controller;


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
    $emails = preg_split('/[;, ]+/', strtolower($emails));

    if ($collective->bundle() == 'collective'){

      $result = self::addToInvites($emails, $collective);
      $collective->save();

      if (count($result['invited'])){
        drupal_set_message(
          t(
            'You have invited %emails to join %collective',
            [
                '%emails' => implode(', ', $result['invited']),
                '%collective' => $collective->getTitle(),
            ]
          ),
          'status',
          TRUE
        );
      }

      if (count($result['reminded'])){
        drupal_set_message(
          t(
            'You have reminded %emails to join %collective',
            [
                '%emails' => implode(', ', $result['reminded']),
                '%collective' => $collective->getTitle(),
            ]
          ),
          'warning',
          TRUE
        );
      }
    }

    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));
  }

  /**
   * Accept a group invitation
   */
  public static function accept($cid = FALSE) {

    $collective = \Drupal::entityTypeManager()->getStorage('node')->load(\Drupal::routeMatch()->getParameter('cid'));

    if ($collective && $collective->bundle() == 'collective'){

      $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());
      $inviteIndexes = self::inviteIndexes($user, $collective);

      if (count($inviteIndexes)) {

        self::addToMembers($user, $collective);
        self::removeFromInvites($inviteIndexes, $collective);
        $collective->save();

        drupal_set_message(
          t('You have joined successfully joined the %collective collective!', ['%collective' => $collective->getTitle()]),
          'status',
          TRUE
        );

        return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));

      } else drupal_set_message(
        t('Oh no! Something went wrong! Please contact !admin', ['!admin' => '<a href="mailto:ict@afrikaburn.com?subject=Lost invitation to ' . $collective->getTitle()  . '">Sanghoma</a>']),
        'warning',
        TRUE
      );

    } else {
      drupal_set_message(
        t('Oh no! This collective no longer exists!'),
        'warning',
        TRUE
      );
    }

    return new RedirectResponse(\Drupal::url('<front>'));
  }

  /**
   * Ignore a group invitation
   */
  public static function ignore($cid = FALSE) {

    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if ($collective->bundle() == 'collective'){

      $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());
      $inviteIndexes = self::inviteIndexes($user, $collective);

      if (count($inviteIndexes)) {

        self::removeFromInvites($inviteIndexes, $collective);
        $collective->save();

        drupal_set_message(t('Invitation ignored'), 'warning', TRUE);
      }
    }

    return new RedirectResponse(\Drupal::url('<front>'));
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
        t('You cannot boot the creator of a collective!'),
        'status',
        TRUE
      );
    }

    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));
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

    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));
  }


  /* ---- CRUD ---- */


  // Add email address to invites
  private static function addToInvites($emails, $collective) {

    $result = [
      'invited' => [],
      'reminded' => [],
    ];

    foreach($emails as $email){

      $email = trim($email);
      $index = self::inviteMailIndex($email, $collective);
      $token = md5($collective->getTitle() . $email . time());

      if ($index !== FALSE){

        $collective->get('field_col_invite_token')->set($index, $token);
        $result['reminded'][] = $email;

      } else {

        $collective->get('field_col_invite_mail')->appendItem(trim($email));
        $collective->get('field_col_invite_token')->appendItem($token);
        $result['invited'][] = $email;

      }
    }

    return $result;
  }

  // Remove from invites
  private static function removeFromInvites($inviteIndexes, $collective) {
    foreach(array_reverse($inviteIndexes) as $index){
      $collective->get('field_col_invite_mail')->removeItem($index);
      $collective->get('field_col_invite_token')->removeItem($index);
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
   * Returns the index of an email invite.
   */
  private static function inviteMailIndex($mail, $collective) {

    $invite_mails = array_column(
      $collective->get('field_col_invite_mail')->getValue(),
      'value'
    );

    return array_search($mail, $invite_mails);
  }

  /**
   * Returns the index of a token invite.
   */
  public static function inviteTokenIndex($token, $collective) {

    $invite_tokens = array_column(
      $collective->get('field_col_invite_token')->getValue(),
      'value'
    );

    return array_search($token, $invite_tokens);
  }

  /**
   * Returns the indexes of a users invite.
   */
  private static function inviteIndexes($user, $collective) {

    $mail_invites = array_column(
      $collective->get('field_col_invite_mail')->getValue(),
      'value'
    );
    $user_mails = array_column(
      array_merge(
        $user->get('mail')->getValue(), $user->get('field_secondary_mail')->getValue()
      ),
      'value'
    );
    $mail_index = array_filter(
      [
        array_search($user_mails[0], $mail_invites),
        array_search($user_mails[1], $mail_invites),

      ],
      'is_int'
    );

    $token_invites = array_column(
      $collective->get('field_col_invite_token')->getValue(),
      'value'
    );
    $token_index = array_search(\Drupal::request()->get('token'), $token_invites);

    return array_filter(
      array_merge($mail_index, [$token_index]),
      'is_int'
    );
  }

  /**
   * Returns the index of a member or admin.
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

