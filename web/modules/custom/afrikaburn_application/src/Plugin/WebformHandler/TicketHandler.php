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

    $form['ticket']['action'] = [
      '#type' => 'radios',
      '#title' => $this->t('Action'),
      '#options' => [
        'add' => 'Add codes',
        'comp' => 'Send complimentaries',
      ],
      '#default_value' => $this->configuration['action'],
      '#required' => TRUE,
    ];

    $form['ticket']['ticket_id'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Ticket IDs'),
      '#options' => [
        'main_ddt_id' => 'Direct Distribution',
        'main_sub_id' => 'Subsidised',
        'main_anathi_id' => 'Anathi',
      ],

      '#default_value' => $this->configuration['ticket_id'],
      '#required' => TRUE,
    ];

    $form['ticket']['num_uses'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of uses'),
      '#min' => 1,
      '#default_value' => $this->configuration['num_uses'],
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
    $this->configuration['action'] = $values['ticket']['action'];
    $this->configuration['ticket_id'] = $values['ticket']['ticket_id'];
    $this->configuration['num_uses'] = $values['ticket']['num_uses'];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(WebformSubmissionInterface $webform_submission) {

    $user = $webform_submission->uid->entity;
    $id_number = $user->field_id_number->value;
    $quicket_code = $user->field_quicket_code->value;
    $quicket_id = $user->field_quicket_id->value;
    $config = \Drupal::config('afrikaburn_shared.settings');
    $tickets = array_keys(array_filter($this->configuration['ticket_id']));
    $num_uses = $this->configuration['num_uses'];

    $ticket_ids = array_map(
      function($ticket) use ($config) {
        return $config->get($ticket);
      },
      $tickets
    );

    $ticket_response = FALSE;

    switch ($this->configuration['action']){

      case 'add':
        if (
          !QuicketController::addTicketTypes(
            $id_number,
            $quicket_code,
            $quicket_id,
            $ticket_ids,
            $num_uses
          )
        ) throw new \Exception('Ticket Exception: Could not add ticket types.');
      break;

      case 'comp':

        $guest = [
          'FirstName' => $user->field_first_name->value,
          'Surname' => $user->field_last_name->value,
          'Email' => $user->field_email->value,
        ];

        if (
          !QuicketController::sendComps(
            [$guest],
            $ticket_ids,
            $num_uses
          )
        ) throw new \Exception('Ticket Exception: Could not send complimentary ticket.');
      break;
    }
  }
}