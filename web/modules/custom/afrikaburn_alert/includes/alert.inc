<?php

/**
 * @file contains alert utilities.
 */


use \Drupal\Core\Link;
use \Drupal\afrikaburn_shared\Utils;
use \Drupal\afrikaburn_alert\Controller\AlertController;
use \Drupal\afrikaburn_collective\Controller\PrivacyController;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;


/* --- Shared variables --- */


/**
  * Computes variables used in alert displays.
  */
function _alert_display_variables(&$variables){

  $alert = $variables['node'];
  $action = $alert->field_alert_action->value;
  $collective = $alert->field_alert_collective->entity;
  $variable_function = '_alert_' . AlertController::$ACTIONS[
    $alert->field_alert_action->value
  ];
  $computed = array_merge(
    [
      'actor' => $alert->uid->entity,
      'picture' => $alert->uid->entity->user_picture,
      'collective' => $alert->field_alert_collective->entity,
    ],
    function_exists($variable_function)
      ? $variable_function($alert, $variables)
      : []
  );

  $variables['picture'] = $computed['picture'] ? $computed['picture']->view(
    [
      'type' => 'image',
      'label' => 'hidden',
      'settings' => [
        'image_style' => 'tiny_25x25',
        'image_link' => 'content',
      ]
    ]
  ) : [];
  $variables['action'] = t(
    explode(':',
      $alert
        ->getFieldDefinitions()['field_alert_action']
        ->getFieldStorageDefinition()
        ->getSetting('allowed_values')[$alert->field_alert_action->value]
    )[(int)$alert->uid->target_id != Utils::currentUser()->id()],
    [
      '@actor' => _label($computed['actor']),
      '@collective' => _label($computed['collective']),
      '@subject' => _label($computed['subject']),
      '@type' => $computed['type'],
      '@stage' => $computed['stage'],
    ]
  );
  $variables['preview'] = $computed['preview'];
  $variables['confirm'] = $computed['confirm'];
  $variables['cancel'] = $computed['cancel'];
  $variables['date'] = t(':time ago', [
    ':time' => \Drupal::service('date.formatter')
      ->formatTimeDiffSince($alert->created->value, ['granularity' => 1])
    ]
  );
}


/* --- Project alert variables --- */


/**
  * Computes variables for create alerts.
  */
function _alert_create($alert){

  module_load_include('inc', 'afrikaburn_registration', 'includes/form');

  $project = $alert->field_alert_project->entity;

  if ($project) {
    $type = _project_form_modes()[$project->bundle()];
    $picture = $project->bundle() == 'performances'
      ? $project->field_prj_wtf_image
      : $project->field_prj_gen_concept;
  }

  return $project ? [
    'subject' => $project,
    'type' => strtolower(
      $type['title']
    ),
    'stage' => strtolower(
      $type['modes'][$alert->field_alert_project_mode->value]
    ),
    'preview' => array_merge(
      $picture->view(
        [
          'type' => 'image',
          'label' => 'hidden',
          'settings' => [
            'image_style' => 'medium',
            'image_link' => 'content',
          ]
        ]
      ),
      ['#suffix' => $project->field_prj_gen_description->value]
    )
  ] : [];
}

/**
  * Computes variables for update alerts.
  */
function _alert_update($alert){
  return _alert_create($alert);
}


/* --- Post alert variables --- */


/**
  * Computes variables for post alerts.
  */
function _alert_post($alert){
  return [
    'subject' => $alert->field_alert_post->entity,
    'preview' => $alert->field_alert_post->entity
      ? ['#markup' => $alert->field_alert_post->entity->comment_body->value]
      : FALSE
  ];
}


/**
  * Computes variables for comment alerts.
  */
function _alert_comment($alert){
  return [
    'subject' => $alert->field_alert_comment->entity,
    'preview' => $alert->field_alert_comment->entity
      ? ['#markup' => $alert->field_alert_comment->entity->comment_body->value]
      : FALSE
  ];
}


/* --- Membership alert variables --- */


/**
  * Computes variables for request alerts.
  */
function _alert_request($alert){

  $requestor = $alert->uid->entity;
  $collective = $alert->field_alert_collective->entity;

  return array_merge(

    [
      'subject' => $collective,
      'preview' => \Drupal::entityTypeManager()
        ->getViewBuilder('user')
        ->view($requestor, 'preview')
    ],

    CollectiveController::isRequested($collective, $requestor)
      ? [

        'confirm' => render(
          Link::createFromRoute(
            'Approve', 'afrikaburn_collective.approve',
            ['cid' => $collective->id(), 'uid' => $requestor->id()],
            [
              'attributes' => ['class' => 'button button-primary'],
              'query' => ['destination' => \Drupal::destination()->get()],
            ]
          )
        ),

        'cancel' => render(
          Link::createFromRoute(
            'Reject', 'afrikaburn_collective.reject',
            ['cid' => $collective->id(), 'uid' => $requestor->id()],
            [
              'attributes' => ['class' => 'button'],
              'query' => ['destination' => \Drupal::destination()->get()],
            ]
          )
        )
      ] : []
  );
}

/**
  * Computes variables for approved alerts.
  */
function _alert_approved($alert){

  $collective = $alert->field_alert_collective->entity;
  $user = $alert->field_alert_user->entity;

  return Utils::currentUser()->id() == $user->id()

    ? array_merge(
      [
        'subject' => $collective,
        'preview' => array_merge(
          $collective->field_picture ? $collective->field_picture->view(
            [
              'type' => 'image',
              'label' => 'hidden',
              'settings' => [
                'image_style' => 'medium',
                'image_link' => 'content',
              ]
            ]
          ) : ['#markup' => ''],
          [
            '#suffix' => render($collective->field_col_description->view('teaser'))
          ]
        )
      ]
    )

    : [
      'subject' => $user,
      'preview' => \Drupal::entityTypeManager()
        ->getViewBuilder('user')
        ->view($user, 'preview')
    ];
}

/**
  * Computes variables for request alerts.
  */
function _alert_invited($alert){

  $invitee = $alert->field_alert_user->entity;
  $collective = $alert->field_alert_collective->entity;

  return array_merge(

    [
      'subject' => $invitee,
      'preview' => array_merge(
        $collective->field_picture ? $collective->field_picture->view(
          [
            'type' => 'image',
            'label' => 'hidden',
            'settings' => [
              'image_style' => 'medium',
              'image_link' => 'content',
            ]
          ]
        ) : ['#markup' => ''],
        [
          '#suffix' => render($collective->field_col_description->view('teaser'))
        ]
      )
    ],

    $invitee && $invitee->id() == Utils::currentUser()->id() &&
    CollectiveController::isInvited($collective,$invitee) ? [

      'confirm' => render(
        Link::createFromRoute(
          'Accept', 'afrikaburn_collective.accept',
          ['cid' => $collective->id(), 'uid' => $invitee->id()],
          [
            'attributes' => ['class' => 'button button-primary'],
            'query' => ['destination' => \Drupal::destination()->get()],
          ]
        )
      ),

      'cancel' => render(
        Link::createFromRoute(
          'Ignore', 'afrikaburn_collective.ignore',
          ['cid' => $collective->id(), 'uid' => $invitee->id()],
          [
            'attributes' => ['class' => 'button'],
            'query' => ['destination' => \Drupal::destination()->get()],
          ]
        )
      )

    ] : []
  );
}

/**
  * Computes variables for boot alerts.
  */
function _alert_booted($alert){
  return [
    'subject' => $alert->field_alert_user->entity,
    'picture' => $alert->field_alert_collective->entity->field_picture
  ];
}

/**
  * Computes variables for boot alerts.
  */
function _alert_banned($alert){
  return [
    'subject' => $alert->field_alert_user->entity,
    'picture' => $alert->field_alert_collective->entity->field_picture
  ];
}


/* --- Utils --- */


/**
 * Creates a suitable label for a target entity.
 */
function _label($target){
  if ($target){
    switch($target->bundle()){

      case 'user':
        $names = array_filter(
          [
            strip_tags(render($target->field_first_name->view('full_name'))),
            strip_tags(render($target->field_last_name->view('full_name'))),
          ]
        );
        $build = [
          '#prefix' => $target->link(),
          '#markup' => count($names)
            ? ' (' . trim(implode(' ', $names)) . ')'
            : '',
        ];
        return render($build);

      case 'art':
      case 'mutant_vehicles':
      case 'performances':
      case 'theme_camps':
      case 'collective':
        return $target->link();

      case 'post':
        return $target->link('post');

    }
  }
}