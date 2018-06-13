<?php

namespace Drupal\afrikaburn_migration\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Database\Database;


/**
 * Class DeleteNodeForm.
 *
 * @package Drupal\afrikaburn_migration\Form
 */
class RebuildUsersForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_rebuild_users';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['operation'] = [
      '#type' => 'radios',
      '#options' => [
        'language' => 'Set default languages',
        'reSave' => 'Rebuild email address field',
        'quicket' => 'Migrate existing quicket info (For migrated users with up to date agreements)',
        'short_agreement' => 'Attach updated agreements (For migrated users with outdated agreements)',
        'new_quicket' => 'Generate new quicket info (For new users with new agreements)',
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Go'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    switch ($form_state->getValues()['operation']){
      case 'language': $this->setLanguage(); break;
      case 'reSave': $this->reSave(); break;
      case 'quicket': $this->migrateQuicket(); break;
      case 'short_agreement': $this->attachAgreementUpdate(); break;
      case 'new_quicket': $this->generateQuicketInfo(); break;
      default: return 'Unknown option!';
    }
  }

  /**
   * Sets all user default languages to en
   */
  public function reSave(){

    $uids = db_query('SELECT uid FROM {users} WHERE uid != 0')->fetchCol();

    $batch = [
      'title' => t('Resaving all users...'),
      'operations' => [],
      'finished' => '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::finished',
    ];

    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::reSave',
        [$uid]
      ];
    }

    batch_set($batch);
  }
  /**
   * Sets all user default languages to en
   */
  public function setLanguage(){

    $uids = db_query('SELECT uid FROM {users} WHERE uid != 0')->fetchCol();

    $batch = [
      'title' => t('Setting language to "en" for all users...'),
      'operations' => [],
      'finished' => '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::finished',
    ];

    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::setLanguage',
        [$uid]
      ];
    }

    batch_set($batch);
  }

  /**
   * Migrates existing quicket codes
   */
  public function migrateQuicket(){

    Database::setActiveConnection('migrate');

    $quicket_data = db_query(
      '
        SELECT
          {field_data_field_quicket_id}.entity_id as uid,
          field_quicket_code_value as code,
          field_quicket_id_value as id
        FROM
          {field_data_field_quicket_code},
          {field_data_field_quicket_id}
        WHERE
          {field_data_field_quicket_id}.entity_id = {field_data_field_quicket_code}.entity_id
      '
    );

    $batch = [
      'title' => t('Migrating quicket codes...'),
      'operations' => [],
      'finished' => '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::finished',
    ];

    foreach($quicket_data as $values){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::setQuicket',
        [$values->uid, $values->code, $values->id]
      ];
    }

    Database::setActiveConnection();
    batch_set($batch);
  }

  /**
   * Attaches the short updated agreement to profiles
   */
  public function attachAgreementUpdate(){

    $uids = db_query('
      SELECT uid
      FROM
        {users} LEFT JOIN {user__field_quicket_code} ON (uid=entity_id)
      WHERE
        entity_id IS NULL AND
        uid <= 38495 AND uid > 0
    ')->fetchCol();

    $aids = array_values(
      \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'agreement')
        ->condition('title', ['Updates to how we do stuff', 'Terms & Conditions'], 'IN')
        ->execute()
    );
    $agreements = \Drupal::entityTypeManager()->getStorage('node')->load($aids);

    $batch = [
      'title' => t('Attaching updated agreement...'),
      'operations' => [],
      'finished' => '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::finished',
    ];

    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::setAgreementUpdate',
        [$uid, $agreements]
      ];
    }

    batch_set($batch);
  }

  /**
   * Fetches new quicket information for profiles
   */
  public function generateQuicketInfo(){

    $uids = db_query('
      SELECT uid
      FROM
        {users} LEFT JOIN {user__field_quicket_code} ON (uid=entity_id)
      WHERE
        entity_id IS NULL AND
        uid > 38495
    ')->fetchCol();

    $batch = [
      'title' => t('Generating new quicket information...'),
      'operations' => [],
      'finished' => '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::finished',
    ];

    foreach($uids as $uid){
      $batch['operations'][] = [
        '\Drupal\afrikaburn_migration\Controller\AfrikaburnUserRebuilder::getNewQuicketInfo',
        [$uid]
      ];
    }

    batch_set($batch);
  }
}