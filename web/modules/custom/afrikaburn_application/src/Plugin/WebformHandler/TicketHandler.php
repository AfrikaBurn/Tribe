<?php

namespace Drupal\afrikaburn_application\Plugin\WebformHandler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionConditionsValidatorInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformTokenManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use \Drupal\afrikaburn_shared\Controller\QuicketController;

/**
 * Webform example handler.
 *
 * @WebformHandler(
 *   id = "ticket_handler",
 *   label = @Translation("Ticket Handler"),
 *   category = @Translation("Ticket Handler"),
 *   description = @Translation("Performs quicket updates."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_IGNORED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */
class TicketHandler extends WebformHandlerBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, WebformSubmissionConditionsValidatorInterface $conditions_validator, WebformTokenManagerInterface $token_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $logger_factory, $config_factory, $entity_type_manager, $conditions_validator);
    $this->tokenManager = $token_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('webform_submission.conditions_validator'),
      $container->get('webform.token_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'message' => 'This is a custom message.',
      'debug' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['ticket'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Ticket settings'),
    ];

    $form['ticket']['ticket_id'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Ticket IDs'),
      '#options' => [
        'main_sub_id' => 'Subsidised',
        'main_anathi_id' => 'Anathi',
      ],

      '#default_value' => $this->configuration['ticket_id'],
      '#required' => TRUE,
    ];

    return $this->setSettingsParents($form);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['ticket_id'] = $values['ticket']['ticket_id'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {

    $template = [
      ['value' => 0],
      ['value' => -1],
      ['value' => -2],
      ['value' => -3],
    ];
    $translate = [
      'main_sub_id' => 2,
      'main_anathi_id' => 3,
    ];

    $user = $webform_submission->uid->entity;
    $id_number = $user->field_id_number->value;
    $config = \Drupal::config('afrikaburn_shared.settings');
    $tickets = array_keys(array_filter($this->configuration['ticket_id']));
    $failed = FALSE;

    $quicket_codes = array_replace(
      $template,
      $user->field_quicket_code->getValue()
    );
    $quicket_ids = array_replace(
      $template,
      $user->field_quicket_id->getValue()
    );

    foreach($tickets as $ticket){

      $quicket_code = $quicket_codes[$translate[$ticket]]['value'];
      $quicket_id = $quicket_ids[$translate[$ticket]]['value'];

      if ($response = QuicketController::addTicketTypes(
        $id_number,
        $quicket_code,
        $quicket_id,
        [$config->get($ticket)],
        1
      )) {
        $quicket_codes[$translate[$ticket]]['value'] = $response->CodeValue;
        $quicket_ids[$translate[$ticket]]['value'] = $response->CodeId;
      } else $failed = TRUE;
    }

    $user->set('field_quicket_code', $quicket_codes);
    $user->set('field_quicket_id', $quicket_ids);
    $user->save();

    if ($failed) throw new \Exception('Ticket Exception: Could not add ticket types.');
  }
}