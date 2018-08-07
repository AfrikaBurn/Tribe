<?php

/**
 * @file Contains afrikaburn collective notification methods.
 */

use \Drupal\user\Entity\User;


/**
 * Notifies collective stakeholders if needed
 */
function _notify_collective($entity){

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $settings = \Drupal::config('afrikaburn_notification.settings');
  $address_settings = \Drupal::config('afrikaburn_notification.address_settings');

  $state = $entity->original ? 'update' : 'new';
  $meta_collective = _meta_collective($entity);
  $diff = _diff($entity);
  $notify_collective = count(
    array_diff(
      array_keys($diff),
      ['Invite Token', 'Invite Mail', 'Members', 'Admins']
    )
  ) > 0;
  $notify_invitees = isset($diff['Invite Token']);

  if ($notify_collective) _notify_collective_changes($entity, $state, $meta_collective, $settings, $address_settings);
  if ($notify_invitees) _notify_collective_invites($entity, $diff, $settings, $address_settings);
}

/**
 * Notify collectives about changes to the collective itself
 */
function _notify_collective_changes($entity, $state, $meta_collective, $settings, $address_settings){

  // Notify collective
  if ($settings->get("collective-collective-$state-collective-enabled")) {
    _notify(
      'no-reply@afrikaburn.com',
      $settings->get('collective-label'),
      $address_settings->get('collective-address'),
      $meta_collective['members'] . ',' . $address_settings->get('archive'),
      $settings->get("collective-collective-$state-collective-subject"),
      $settings->get("collective-collective-$state-collective-body"),
      $entity
    );
  }

  // Notify wranglers
  if ($settings->get("collective-collective-$state-wranglers-enabled")) {

    $user = User::load(\Drupal::currentUser()->id());

    _notify(
      $address_settings->get('collective-address'),
      $user->field_first_name->value . " '" . $user->name->value . "' " . $user->field_last_name->value . ' via AfrikaBurn Tribe',
      'no-reply@afrikaburn.com',
      $address_settings->get('archive'),
      $settings->get("collective-collective-$state-wranglers-subject"),
      $settings->get("collective-collective-$state-wranglers-body"),
      $entity
    );
  }
}

/**
 * Send invitations and notify collective about invitations
 */
function _notify_collective_invites($entity, $diff, $settings, $address_settings){

  $latest = array_column($entity->get('field_col_invite_mail')->getValue(), 'value');
  $invite = array_diff(
    $latest,
    array_column($entity->original->get('field_col_invite_mail')->getValue(), 'value')
  );
  $remind = [];
  $changed = array_diff($diff['Invite Token'][1], $diff['Invite Token'][0]);
  foreach($changed as $token){
    $remind[] = $latest[
      \Drupal\afrikaburn_collective\Controller\MemberController::inviteTokenIndex(
        $token,
        $entity
      )
    ];
  }
  $remind = array_diff($remind, $invite);

  // Send new invitations
  if (count($invite) && $settings->get('collective-invitation-new-invitees-enabled')) {
    _notify(
      'no-reply@afrikaburn.com',
      $settings->get('collective-label'),
      $address_settings->get('collective-address'),
      implode(',', $invite) . ', ' . $address_settings->get('archive'),
      $settings->get("collective-invitation-new-invitees-subject"),
      $settings->get("collective-invitation-new-invitees-body"),
      $entity
    );
  }

  // Send reminders
  if (count($remind) && $settings->get('collective-invitation-update-invitees-enabled')) {
    _notify(
      'no-reply@afrikaburn.com',
      $settings->get('collective-label'),
      $address_settings->get('collective-address'),
      implode(',', $invite) . ', ' . $address_settings->get('archive'),
      $settings->get("collective-invitation-update-invitees-subject"),
      $settings->get("collective-invitation-update-invitees-body"),
      $entity
    );
  }
}