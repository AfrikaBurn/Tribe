<?php

/**
 * @file Contains afrikaburn collective notification methods.
 */

use \Drupal\user\Entity\User;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_shared\Utils;

/**
 * Notifies collective stakeholders if needed
 */
function _notify_collective($entity){

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $settings = \Drupal::config('afrikaburn_notification.settings');
  $address_settings = \Drupal::config('afrikaburn_notification.address_settings');

  $state = $entity->original ? 'update' : 'new';
  $meta_collective = _meta_collective($entity);
  $diff = Utils::diff($entity);
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

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  // Notify collective
  if (
    $settings->get("collective-collective-$state-collective-enabled") &&
    CollectiveController::setting($entity, 'emails')
  ) {
    _notify(
      'no-reply@afrikaburn.com',
      $settings->get('collective-label'), $address_settings->get('collective-address'),
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
      $user->field_first_name->value . " '" . $user->name->value . "' " . $user->field_last_name->value . ' via AfrikaBurn Tribe', 'no-reply@afrikaburn.com',
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

  module_load_include('inc', 'afrikaburn_notification', 'includes/util');

  $tokens = array_column($entity->get('field_col_invite_token')->getValue(), 'value');
  $mails = array_column($entity->get('field_col_invite_mail')->getValue(), 'value');
  $token_map = array_combine($mails, $tokens);

  $invite = array_diff(
    $mails,
    array_column($entity->original->get('field_col_invite_mail')->getValue(), 'value')
  );
  $remind = [];
  $changed = array_diff($diff['Invite Token'][1], $diff['Invite Token'][0]);

  foreach($changed as $token){
    $remind[] = $mails[
      array_search(
        $token,
        array_column(
          $entity->get('field_col_invite_token')->getValue(),
          'value'
        )
      )
    ];
  }
  $remind = array_diff($remind, $invite);

  // Send new invitations
  if (count($invite) && $settings->get('collective-invitation-new-invitees-enabled')) {
    foreach($invite as $invitee){

      $accept = \Drupal::request()->getSchemeAndHttpHost() . '/collective/' . $entity->id() . '/accept?token=' . $token_map[$invitee];
      $ignore = \Drupal::request()->getSchemeAndHttpHost() . '/collective/' . $entity->id() . '/ignore?token=' . $token_map[$invitee];
      $body = str_replace(
        ['[accept]', '[ignore]'],
        [$accept, $ignore],
        $settings->get("collective-invitation-new-invitees-body")
      );

      _notify(
        'no-reply@afrikaburn.com',
        $settings->get('collective-label'), $address_settings->get('collective-address'),
        $invitee . ', ' . $address_settings->get('archive'),
        $settings->get("collective-invitation-new-invitees-subject"),
        $body,
        $entity
      );
    }
  }

  // Send reminders
  if (count($remind) && $settings->get('collective-invitation-update-invitees-enabled')) {
    foreach($remind as $remindee){

      $accept = \Drupal::request()->getSchemeAndHttpHost() . '/collective/' . $entity->id() . '/accept?token=' . $token_map[$remindee];
      $ignore = \Drupal::request()->getSchemeAndHttpHost() . '/collective/' . $entity->id() . '/ignore?token=' . $token_map[$remindee];
      $body = str_replace(
        ['[accept]', '[ignore]'],
        [$accept, $ignore],
        $settings->get("collective-invitation-update-invitees-body")
      );

      _notify(
        'no-reply@afrikaburn.com',
        $settings->get('collective-label'), $address_settings->get('collective-address'),
        $remindee . ', ' . $address_settings->get('archive'),
        $settings->get("collective-invitation-update-invitees-subject"),
        $body,
        $entity
      );
    }
  }
}