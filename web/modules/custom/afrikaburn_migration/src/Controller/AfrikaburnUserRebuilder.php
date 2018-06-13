<?php
/**
 * @file
 * Contains \Drupal\afrikaburn_migration\AfrikaburnUserRebuilder.
 */

namespace Drupal\afrikaburn_migration\Controller;


use Drupal\Core\Controller\ControllerBase;


class AfrikaburnUserRebuilder extends ControllerBase {

  /* ---- Set default languages ---- */

  public static function reSave($uid, &$context) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);

    $user->field_email = $user->mail;

    $context['results'][] = $user->save();
    $context['message'] = 'reSaving users';

  }

  /* ---- Set default languages ---- */

  public static function setLanguage($uid, &$context) {

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    $user->langcode = 'en';
    $user->preferred_langcode = 'en';
    $user->admin_langcode = NULL;

    $context['results'][] = $user->save();
    $context['message'] = 'Setting default languages';

  }

  /* ---- Set quicket code ---- */

  public static function setQuicket($uid, $code, $id, &$context){

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user) {

      $user->field_quicket_code = $code;
      $user->field_quicket_id = $id;

      $context['results'][] = $user->save();
      $context['message'] = 'Updating quicket codes';
    }

  }

  /* ---- Set updated agreement ---- */

  public static function setAgreementUpdate($uid, $agreements, &$context){

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user) {

      if ($user->get('field_agreements')->count() == 0){
        foreach($agreements as $agreement){
          $user->get('field_agreements')->appendItem($agreement);
        }
        $context['results'][] = $user->save();
      }

      $context['message'] = 'Attaching updated agreement';
    }

  }

  /* ---- Set updated agreement ---- */

  public static function getNewQuicketInfo($uid, &$context){

    $user = \Drupal::entityTypeManager()->getStorage('user')->load($uid);
    if ($user) {

      module_load_include('inc', 'afrikaburn_shared', 'includes/quicket');

      $quicket = _quicket(
        'POST',
        $user->get('field_id_number')->value,
        $user->hasField('field_teens') ? $user->get('field_teens')->value : 0,
        $user->hasField('field_kids') ? $user->get('field_kids')->value : 0
      );

      $user->field_quicket_code = $quicket['code'];
      $user->field_quicket_id = $quicket['id'];

      $context['results'][] = $user->save();
      $context['message'] = 'Generating new quicket data';
    }

  }

  /* ---- Finished ---- */

  public static function finished($success, $results, $operations) {

    drupal_set_message(
      $success
        ? \Drupal::translation()->formatPlural(
            count($results),
            'One user processed.', '@count users processed.'
          )
        : t('Finished with errors.')
    );
  }
}