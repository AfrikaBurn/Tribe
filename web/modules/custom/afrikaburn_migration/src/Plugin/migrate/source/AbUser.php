<?php 

/**
 * @file
 * Contains \Drupal\afrikaburn_migration\Plugin\migrate\source\AbUser.
 */
 
namespace Drupal\afrikaburn_migration\Plugin\migrate\source;
 
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;
 
/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "afrikaburn_user"
 * )
 */
class AbUser extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 0, '>');
  }
 
  /**
   * {@inheritdoc}
   */
  public function fields() {

    $fields = $this->baseFields();

    $fields['first_name'] = $this->t('First name');
    $fields['last_name'] = $this->t('Last name');    
    $fields['gender'] = $this->t('Gender');
    $fields['date_of_birth'] = $this->t('Date of birth');
    $fields['sa_id_or_passport_number'] = $this->t('ID or Passport number');
    $fields['drivers_licence_number'] = $this->t('Drivers licence number');
    $fields['mobile_number'] = $this->t('Mobile');
    $fields['secondary_email_address'] = $this->t('Alternate email address');
    $fields['where_are_you_based'] = $this->t('Country');
    $fields['munciple_district'] = $this->t('Municipal district');
    $fields['previous_envolvement'] = $this->t('Participation');
    $fields['other_burns'] = $this->t('Other burns');
    // $fields['newsletter'] = $this->t('Receive newsletter');
    $fields['website'] = $this->t('Social media links');

    return $fields;
  }
 
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {

    $uid = $row->getSourceProperty('uid');
 
    $this->prepField($uid, $row, 'first_name');
    $this->prepField($uid, $row, 'last_name');
    $this->prepField($uid, $row, 'gender');
    $this->prepField($uid, $row, 'date_of_birth');
    $this->prepField($uid, $row, 'sa_id_or_passport_number');
    $this->prepField($uid, $row, 'drivers_licence_number');
    $this->prepField($uid, $row, 'mobile_number');
    $this->prepField($uid, $row, 'secondary_email_address', '_email');
    $this->prepField($uid, $row, 'where_are_you_based', '_iso2');
    $this->prepTaxField($uid, $row, 'munciple_district', '_tid');
    $this->prepComField($uid, $row);
    $this->prepField($uid, $row, 'other_burns');
    $this->prepField($uid, $row, 'website', '_url');

    $row->setSourceProperty('langcode', 'en');
    $row->setSourceProperty('preferred_langcode', 'en');
    $row->setSourceProperty('preferred_admin_langcode', NULL);

    return parent::prepareRow($row);
  }
 
  /**
   * Prepares a field
   * @param  [Row] $row        [description]
   * @param  [string] $field_name [description]
   */
  public function prepField($uid, &$row, $field_name, $suffix = '_value'){

    $result = $this->getDatabase()->query('
      SELECT
        fld.field_' . $field_name . $suffix .'
      FROM
        {field_data_field_' . $field_name . '} fld
      WHERE
        fld.entity_id = :uid', 
      array(':uid' => $uid)
    );

    foreach ($result as $record) {
      $record = (array)$record;
      $row->setSourceProperty($field_name, $record['field_' . $field_name . $suffix]);
    }    
  }

  /**
   * Prepares a field
   * @param  [Row] $row        [description]
   * @param  [string] $field_name [description]
   */
  public function prepTaxField($uid, &$row, $field_name, $suffix = '_value'){

    $result = $this->getDatabase()->query('
      SELECT
        ttd.name as term_name
      FROM
        {field_data_field_' . $field_name . '} fld,
        {taxonomy_term_data} ttd
      WHERE
        ttd.tid = fld.field_' . $field_name . $suffix .' AND
        fld.entity_id = :uid', 
      array(':uid' => $uid)
    );

    foreach ($result as $record) {
      $record = (array)$record;
      $row->setSourceProperty($field_name, $record['term_name']);
    }    
  }

  /**
   * Prepares a field
   * @param  [Row] $row        [description]
   * @param  [string] $field_name [description]
   */
  public function prepComField($uid, &$row){

    $result = $this->getDatabase()->query("
      SELECT
        ttd.name as name,
        field_previous_envolvement_field_previous_participation_value as value
      FROM
        {field_data_field_previous_envolvement} fld,
        {taxonomy_term_data} ttd
      WHERE
        ttd.tid = field_previous_envolvement_field_year_and_event_name_tid AND
        fld.entity_id = :uid", 
      array(':uid' => $uid)
    );

    foreach ($result as $record) {
      $record = (array)$record;
      $row->setSourceProperty('previous_envolvement', $record['name'] . ': ' . $record['value']);
    }    
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'uid' => array(
        'type' => 'integer',
        'alias' => 'u',
      ),
    );
  }
 
  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {

    $fields = array(
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'status' => $this->t('Status'),
      'timezone' => $this->t('Timezone'),
      'language' => $this->t('Language'),
      'picture' => $this->t('Picture'),
      'init' => $this->t('Init'),
    );

    return $fields; 
  }
 
  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }
 
  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'user';
  }
 
}
