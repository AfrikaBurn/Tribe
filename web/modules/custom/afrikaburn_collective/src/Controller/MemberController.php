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


  /* ----- Joining ----- */


  /**
   * Join a group
   */
  public static function join($cid){

    module_load_include('inc', 'afrikaburn_collective', 'includes/util');
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if (afrikaburn_collective_setting('open', $collective)) {

      $user = \Drupal::entityTypeManager()->getStorage('user')->load(\Drupal::currentUser()->id());

      if (!afrikaburn_collective_setting('vetted', $collective)) {

        self::addToMembers($user, $collective);
        $collective->save();

        drupal_set_message(
          t('You have joined successfully joined the %collective collective!', ['%collective' => $collective->getTitle()]),
          'status',
          TRUE
        );
      } else {

        self::addToRequests($user, $collective);
        $collective->save();

        drupal_set_message(
          t('You have successfully requested to join the %collective collective. Your request is awaiting moderation.', ['%collective' => $collective->getTitle()]),
          'status',
          TRUE
        );
      }
    } else {
        drupal_set_message(
          t('The %collective collective no longer accepts join requests!', ['%collective' => $collective->getTitle()]),
          'warning',
          TRUE
        );
    }

    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));
  }

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

      } else {
        $error = [
          '#markup' => '<a href="mailto:ict@afrikaburn.com?subject=Lost invitation to ' . $collective->getTitle()  . '">Sanghoma</a>'
        ];

        drupal_set_message(
          t(
            'Oh no! Something went wrong! Please contact !admin',
            ['!admin' => drupal_render($error)]
          ),
          'warning',
          TRUE
      );
    }

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
   * Approve a membership application
   */
  public static function approve($cid = FALSE, $uid = FALSE) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if (!count(self::memberIndex($uid, $collective, 'field_col_members'))){
      self::addToMembers($user, $collective);
      self::removeFromRequests($user, $collective);
      $collective->save();

      drupal_set_message(
        t('Membership approved!'),
        'status',
        TRUE
      );
    }

    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));
  }

  /**
   * Reject a membership application
   */
  public static function reject($cid = FALSE, $uid = FALSE) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    self::removeFromRequests($user, $collective);
    $collective->save();

    drupal_set_message(
      t('Membership request rejected!'),
      'warning',
      TRUE
    );

    return new RedirectResponse(\Drupal::url('entity.node.canonical', ['node' => $cid]));
  }

  /* ----- Leaving ----- */


  /**
   * Leave a group
   */
  public static function leave($cid = FALSE) {

    $uid = \Drupal::currentUser()->id();
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if ($collective->bundle() == 'collective'){

      $member_index = self::memberIndex($uid, $collective, 'field_col_members');
      $admin_index = self::memberIndex($uid, $collective, 'field_col_admins');

      self::removeFromMembers($member_index, $collective);
      self::removeFromAdmins($admin_index, $collective);
      $collective->save();
    }

    drupal_set_message(
      t(
        'You have left %collective',
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


  /* ----- Role changes ----- */


  /**
   * Unadmin
   */
  public static function strip($cid = FALSE, $uid = FALSE) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $collective = \Drupal::entityTypeManager()->getStorage('node')->load($cid);

    if($collective->getOwner()->id() != $uid){

      if (isset($user) && $collective->bundle() == 'collective'){
        $admin_index = self::memberIndex($uid, $collective, 'field_col_admins');
        self::removeFromAdmins($admin_index, $collective);
        $collective->save();
      }

      drupal_set_message(
        t(
          '%user has been stripped of Admin privileges in %collective',
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
        t('You cannot strip the creator of a collective!'),
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


  // Add to requests
  private static function addToRequests($user, $collective) {
    if (!self::hasRequested($collective)){
      $collective->get('field_col_requests')->appendItem($user);
    }
  }

  // Remove from requests
  private static function removeFromRequests($user, $collective) {

    $indexes = self::memberIndex($user->id(), $collective, 'field_col_requests');

    if (count($indexes)){
      foreach(array_reverse($indexes) as $index){
        $collective->get('field_col_requests')->removeItem($index);
      }
    }
  }

  // Add email address to invites
  private static function addToInvites($emails, $collective) {

    $result = [
      'invited' => [],
      'reminded' => [],
    ];

    foreach($emails as $email){

      if (preg_match('/[^@]+@[^\.]+\..+/', $email) && !preg_match('/[^a-z0-9\.\+\-\_\@]/', $email)){

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
      } else {
        drupal_set_message(
          $email .
          t(' does not seem to be a valid email address, please check it and try inviting it again.'), 'warning', TRUE
        );
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

    $token_invites = array_column(
      $collective->get('field_col_invite_token')->getValue(),
      'value'
    );

    return array_unique(
      array_filter(
        [
          array_search(\Drupal::request()->get('token'), $token_invites),
          array_search(
            strtolower($user->mail->value),
            array_map('strtolower', $mail_invites)
          )
        ],
        'is_int'
      )
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

  /**
   * Checks whether the current user is an admin
   */
  public static function hasRequested($collective){
    $uid = \Drupal::currentUser()->id();
    return $collective
      ? !in_array(
          array_search(
            $uid,
            array_column(
              $collective->get('field_col_requests')->getValue(), 'target_id'
            )
          ),
          [NULL, FALSE],
          TRUE
        )
      : FALSE;
  }
}
