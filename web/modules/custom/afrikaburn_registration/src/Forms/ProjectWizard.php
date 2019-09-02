<?php

/**
 * @file
 * Contains \Drupal\afrikaburn_registration\Forms\ProjectWizard.
 */

namespace Drupal\afrikaburn_registration\Forms;


use \Drupal\Core\Form\FormBase;
use \Drupal\Core\Form\FormStateInterface;
use \Drupal\user\Entity\User;
use \Drupal\views\Views;

use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\afrikaburn_registration\Controller\RegistrationController;


/**
 * Defines a form that configures forms module settings.
 */
class ProjectWizard extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'afrikaburn_registration_wizard';
  }


  /* ----- Form Builder ----- */


  const
    INTRODUCTION =
      'This wizard will help you set up or reuse a Project and
       Collective space to help you collaborate with team mates, new ones can
       find you, share resources and register your <strong>Artwork, Binnekring
       event, Mutant vehicle or Theme camp</strong>',
    CLOSED =
      'All project registrations are closed at present.<p>You can still
      <a class="button" href="@create_collective">create a Collective</a> to
      help you collaborate with team mates, new ones can find you and share
      resources.</p>';

  /**
   * {@inheritdoc}
   */
  public function buildForm($form, $form_state, $request = NULL) {

    module_load_include('inc', 'afrikaburn_registration', 'includes/form');

    $form['#wizard'] = TRUE;
    $config = \Drupal::config('afrikaburn_registration.settings');
    $types = _project_form_modes();
    $keys = array_keys($types);
    $open_registrations = count(
      array_filter(
        $keys,
        function($option, $key) use ($config){
          return $config->get($option . '/form_1')['open'];
        },
        ARRAY_FILTER_USE_BOTH
      )
    );

    if ($open_registrations) {

      $form['introduction'] = [
        '#prefix' => '<p>',
        '#markup' => $this->t(self::INTRODUCTION),
        '#suffix' => '</p>',
      ];

      $form['tabs'] = [

        '#type' => 'horizontal_tabs',
        '#entity_type' => 'node',
        '#group_name' => 'project_tabs',
        '#bundle' => 'collective',

        '#prefix' => '<div class="field-group-tabs-wrapper">',

        'ready' => $this->ready($form_state),
        'steady' => $this->steady($form_state),
        'go' => $this->go($form_state),

        '#suffix' => '</div>',
      ];

      $form['actions'] = [
        '#type' => 'container',
        'create' => [
          '#type' => 'submit',
          '#value' => $this->t('GO'),
          '#attributes' => ['class' => ['button--primary']],
        ],
        '#attributes' => ['class' => ['form-actions']],
      ];
    } else $form[] = [
      '#markup' => $this->t(
        self::CLOSED,
        ['@create_collective' => '/node/add/collective']
      )
    ];

    $form['#attached']['library'][] = 'afrikaburn_registration/wizard';
    $form['#attached']['library'][] = 'afrikaburn_shared/wizard';
    $form['#attributes'] = ['class' => 'js-wizard'];

    return $form;
  }


  /* ----- Form Validator ----- */


  const
    PROJECT_TODO_SELECT_ERROR =
      'Please select whether to reuse or create a Project!',
    COLLECTIVE_TODO_SELECT_ERROR =
      'Please select whether to reuse or create a Collective!';


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    if (!$form_state->getValue('project_todo')){
      $form_state->setErrorByName(
        'project_todo_new',
        $this->t(self::PROJECT_TODO_SELECT_ERROR)
      );
    }

    if (!$form_state->getValue('collective_todo')){
      $form_state->setErrorByName(
        'collective_todo_new',
        $this->t(self::COLLECTIVE_TODO_SELECT_ERROR)
      );
    }

    if ($form_state->getValue('new_collective')['name'] == 'AfrikaBurn'){
      $form_state->setErrorByName(
        'new_collective[name',
        $this->t('There can be only one AfrikaBurn Collective!')
      );
    }
  }


  /* ----- Form Submitter ----- */


  const
    SETTINGS_OPEN = [
      'public',
      'public_members',
      'open',
      'members_invite',
      'projects',
      'emails',
    ],
    SETTINGS_CLOSED = [
      'public',
      'public_members',
      'members_invite',
      'projects',
      'emails',
    ],
    SETTINGS_PRIVATE = [
      'members_invite',
      'projects',
      'emails',
    ];


  /**
   * {@inheritdoc}
   */
  public function submitForm(&$form, $form_state) {

    $values = $form_state->getValues();
    $storage = \Drupal::entityTypeManager()->getStorage('node');

    if ($values['collective_todo'] == 'existing'){
      $collective = $storage->load($values['existing_collective']['collective']);
    } else {

      $collective = $storage->create(
        [
          'type'  => 'collective',
          'title' => $values['new_collective']['name'],
          'field_col_description' => $values['new_collective']['description'],
          'status' => 1,
          'field_settings' => ([
            'open' => self::SETTINGS_OPEN,
            'closed' => self::SETTINGS_CLOSED,
            'private' => self::SETTINGS_PRIVATE,
          ])[$values['new_collective']['permissions']]
        ]
      );

      $collective->save();
    }

    if ($values['emails']) {
      CollectiveController::bulkInvite($collective, $values['emails']);
    }

    if ($values['project_todo'] == 'existing') {
      RegistrationController::reuse($values['existing_project']['project'], $collective->id());
    } else {
      $registration = $storage->create(
        [
          'type'  => $values['new_project']['type'],
          'title' => $values['new_project']['title'],
          'field_prj_gen_description' => $values['new_project']['description'],
          'field_collective' => $collective,
          'status' => 0,
        ]
      );

      $registration->save();
    }

    $url = \Drupal\Core\Url::fromUri('internal:/node/' . $collective->id());
    $url->setOption('query', ['expand' => 'block-projectregistrations']);
    drupal_set_message('
      Well done! Look for your project under the Projects header in the
      right-hand column to complete and submit your registration.
    ');
    $form_state->setRedirectUrl($url);
  }


  /* ----- Form steps ----- */


  /* --- Ready --- */


  const
    PROJECT_TODO_REUSE =
      'Not your first rodeo? You\'ve registered this Project before and would
      like to reuse that registration',
    PROJECT_TODO_NEW =
      'This Project is brand new and you\'ve not registered it before, or want
      to register it from scratch',
    PROJECT_SELECT_ERROR =
      'Please select an existing registration!';


  /**
   * Ready step builder
   */
  private function ready($form_state){

    $input = $form_state->getUserInput();

    $tab = [
      '#title' => 'Ready',
      '#group_name' => 'ready',
      '#bundle' => 'collective',
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => ['id' => 'ready', 'class' => ['field-group-tab']],
      'content' => [],
    ];

    $config = \Drupal::config('afrikaburn_registration.settings');
    $types = _project_form_modes();
    $keys = array_keys($types);
    $labels = array_column($types, 'title');
    $open_registrations = array_filter(
      array_combine($keys, $labels),
      function($option, $key) use ($config){
        return $config->get($key . '/form_1')['open'];
      },
      ARRAY_FILTER_USE_BOTH
    );
    $closed = array_diff(
      $labels,
      $open_registrations
    );
    $last = array_pop($closed);
    $closed_description = $this->t(
      '%type registration is closed at present',
      [
        '%type' => count($closed)
          ? implode($closed, ', ') . ' and ' . $last
          : $last
      ]
    );

    $tab['content'] = [

      'project_todo' => ['#type' => 'hidden'],

      'project_todo_reuse' => [

        '#title' => $this->t('Reuse an existing Project registration'),
        '#type' => 'radio',
        '#name' => 'project_todo',
        '#default_value' => $input['project_todo'] == 'existing'
          ? 'existing'
          : FALSE,
        '#attributes' => array_merge(
          ['value' => 'existing'],
          $input['project_todo'] == 'existing' ? ['checked' => 'checked'] : []
        ),
        '#description' => $this->t(self::PROJECT_TODO_REUSE),
      ],

      'existing_project' => [
        '#type' => 'container',
        '#attributes' => ['class' => 'form-item'],
        '#tree' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="project_todo"]' => ['value' => 'existing'],
          ],
        ],

        ['#markup' => '<div class="description">'],

        [
          '#type' => 'container',
          '#attributes' => [
            'name' => 'existing_project[project]_error',
            'class' => [
              'form-item--error-message error',
              ($input['existing_project']['project'] ? '' : 'hidden')
            ]
          ],
          ['#markup' => $this->t(self::PROJECT_SELECT_ERROR)],
        ],

        'project' => [
          '#title' => $this->t('Select a registration'),
          '#type' => 'radios',
          '#name' => 'project',
          '#options' => [],
          '#states' => [
            'visible' => [
              ':input[name="project_todo"]' => ['value' => 'existing'],
            ],
            'required' => [
              ':input[name="project_todo"]' => ['value' => 'existing'],
            ],
          ],
        ],

        ['#markup' => '</div>'],
      ],

      'project_todo_new' => [
        '#title' => $this->t('Start from scratch'),
        '#type' => 'radio',
        '#name' => 'project_todo',
        '#attributes' => array_merge(
          ['value' => 'new'],
          $input['project_todo'] == 'new' ? ['checked' => 'checked'] : []
        ),
        '#value' => 'new',
        '#description' => $this->t(self::PROJECT_TODO_NEW),
      ],

      'new_project' => [

        '#type' => 'container',
        '#attributes' => ['class' => 'form-item new-project-fields'],
        '#tree' => TRUE,
        '#states' => [
          'visible' => [':input[name="project_todo"]' => ['value' => 'new']]
        ],

        ['#markup' => '<div class="description">'],

        [
          '#type' => 'container',
          '#attributes' => [
            'name' => 'new_project[type]_error',
            'class' => ['form-item--error-message hidden error']
          ],
          ['#markup' => $this->t('Please a Project type:')],
        ],

        'type' => [
          '#title' => $this->t('What is it?'),
          '#type' => 'radios',
          '#options' => $open_registrations,
          '#states' => [
            'required' => [
              ':input[name="project_todo"]' => ['value' => 'new'],
            ],
          ],
          '#description' => $closed_description,
        ],

        'title' => [
          '#type' => 'textfield',
          '#title' => $this->t('Title'),
          '#description' => $this->t('A title or name for your Project.'),
          '#states' => [
            'required' => [
              ':input[name="project_todo"]' => ['value' => 'new'],
            ],
          ],
        ],

        'description' => [
          '#type' => 'textarea',
          '#title' => $this->t('Description'),
          '#description' => $this->t('A short description of your Project.'),
          '#states' => [
            'required' => [
              ':input[name="project_todo"]' => ['value' => 'new'],
            ],
          ],
        ],

        ['#markup' => '</div>'],
      ],

      [
        '#type' => 'container',
        '#attributes' => [
          'name' => 'project_todo_error',
          'class' => ['form-item--error-message hidden error'],
        ],
        ['#markup' => $this->t('Please select one of these options')],
      ]
    ];

    $view = Views::getView('my_projects');
    $view->setDisplay('select_project');
    $view->execute();

    if ($view->total_rows == 0){
      $tab['content']['project_todo_reuse']['#access'] = FALSE;
      $tab['content']['project_todo_new']['#attributes']['checked'] = 'checked';
    } else foreach($view->result as $row){

      $node = $row->_entity;
      $bundle = $node->bundle();

      $option = [
        '#type' => 'container',
        'image' => $node->field_prj_gen_concept &&
                   $node->field_prj_gen_concept->first()
        ? [
          '#theme' => 'image_style',
          '#style_name' => 'thumbnail',
          '#uri' => $node->field_prj_gen_concept
            ->first()
            ->entity
            ->getFileUri(),
        ] : [],
        'title' => [
          '#prefix' => '<h3>',
          '#markup' => $node->title->value,
          '#suffix' => '</h3>',
        ],
        'description' => [
          '#prefix' => '<div>',
          '#markup' => $node->field_prj_gen_description->value,
          '#suffix' => '</div>',
        ],
        'collective' => [
          '#type' => 'container',
          '#attributes' => [
            'class' => 'collective',
            'data-cid' => $node->field_collective->target_id,
          ],
          $node->field_collective->entity->field_picture->first() ? [
            '#theme' => 'image_style',
            '#style_name' => 'tiny_25x25',
            '#uri' => $node
              ->field_collective
              ->entity
              ->field_picture
              ->first()
              ->entity
              ->getFileUri(),
          ] : [],
          [
            '#markup' => $node
              ->field_collective
              ->entity
              ->title
              ->value . ' (' . $node
              ->field_year_cycle
              ->value . ')',
          ],
        ],
      ];
      $tab['content']
        ['existing_project']
        ['project']
        ['#options']
        [$row->nid] = render($option);
      if (!$open_registrations[$bundle]){
        $tab['content']
        ['existing_project']
        ['project']
        [$row->nid] = [
          '#disabled' => TRUE,
          '#description' => $this->t(
            '%type registration is closed at present.',
            [
              '%type' => $types[$bundle]['title'],
            ]
          ),
          '#attributes' => [
            'class' => ['disabled'],
          ],
          '#weight' => 2,
        ];
      }
    }

    return $tab;
  }


  /* --- Steady --- */


  const
    COLLECTIVE_TODO_REUSE =
      'If you\'re an Admin of a group that registers projects, you can use any
      of those for this Project.',
    COLLECTIVE_TODO_NEW =
      'A Collective is a group of people who do things together. Things like
      creating an artwork, setting up a binnekring event, build a mutant
      vehicle, organise a theme camp or just camp together and hang out.',
    COLLECTIVE_SELECT_ERROR =
      'Please select a Collective';

  /**
   * Steady step builder
   */
  private function steady($form_state){

    $user = User::load(\Drupal::currentUser()->id());
    $input = $form_state->getUserInput();

    $tab = [
      '#title' => 'Steady',
      '#group_name' => 'steady',
      '#bundle' => 'collective',
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => ['id' => 'steady', 'class' => ['field-group-tab']],
      'content' => [

        'collective_todo' => ['#type' => 'hidden'],

        'collective_todo_reuse' => [
          '#title' => $this->t('Use an existing Collective'),
          '#type' => 'radio',
          '#name' => 'collective_todo',
          '#description' => $this->t(self::COLLECTIVE_TODO_REUSE),
          '#attributes' => array_merge(
            ['value' => 'existing'],
            $input['collective_todo'] == 'new' ? ['checked' => 'checked'] : []
          ),
          '#states' => [
            'checked' => [
              ':input[name="project_todo"]' => ['value' => 'existing'],
            ],
          ],
        ],

        'existing_collective' => [
          '#type' => 'container',
          '#attributes' => ['class' => 'form-item'],
          '#tree' => TRUE,
          '#states' => [
            'visible' => [
              ':input[name="collective_todo"]' => ['value' => 'existing'],
            ]
          ],

          ['#markup' => '<div class="description">'],

          [
            '#type' => 'container',
            '#attributes' => [
              'name' => 'existing_collective[collective]_error',
              'class' => ['form-item--error-message hidden error'],
            ],
            ['#markup' => $this->t(self::COLLECTIVE_SELECT_ERROR)],
          ],
          'collective' => [
            '#title' => $this->t('Select a Collective'),
            '#name' => 'exising_collective',
            '#type' => 'radios',
            '#options' => [],
            '#states' => [
              'optional' => [
                ':input[name="collective_todo"]' => ['value' => 'new'],
              ],
            ],
          ],
          ['#markup' => '</div>'],
        ],


        'collective_todo_new' => [
          '#title' => $this->t('Create a new Collective'),
          '#type' => 'radio',
          '#name' => 'collective_todo',
          '#attributes' => array_merge(
            ['value' => 'new'],
            $input['project_todo'] == 'new' ? ['checked' => 'checked'] : []
          ),
          '#description' => $this->t(self::COLLECTIVE_TODO_NEW),
          '#states' => [
            'checked' => [':input[name="project_todo"]' => ['value' => 'new']],
          ],
        ],
        'new_collective' => [
          '#type' => 'container',
          '#tree' => TRUE,
          '#attributes' => ['class' => 'form-item new-collective-fields'],
          '#states' => [
            'visible' => [
              ':input[name="collective_todo"]' => ['value' => 'new'],
            ],
          ],
          'name' => [
            '#prefix' => '<div class="description">',
            '#type' => 'textfield',
            '#title' => $this->t('Name'),
            '#description' => $this->t('A title or name for your Collective.'),
            '#states' => [
              'required' => [
                ':input[name="collective_todo"]' => ['value' => 'new'],
              ]
            ],
          ],
          'description' => [
            '#type' => 'textarea',
            '#title' => $this->t('Description'),
            '#description' => $this->t('A short intro to your Collective.'),
            '#states' => [
              'required' => [
                ':input[name="collective_todo"]' => ['value' => 'new'],
              ]
            ],
          ],
          'permissions' => [
            '#type' => 'radios',
            '#title' => 'How it works',
            '#options' => [
              'open' => 'Open - Allow people to find and join your Collective',
              'closed' => 'Closed - Allow people to find your Collective, but
                           not join without approval',
              'private' => 'Private - Allow only invited people to find or join
                           your Collective',
            ],
            '#default_value' => 'closed',
            '#description' => 'You can make these permissions more specific when
                               you edit your Collective.',
          ],
          '#suffix' => '</div>',
        ],

        [
          '#type' => 'container',
          '#attributes' => [
            'name' => 'collective_todo_error',
            'class' => ['form-item--error-message hidden error'],
          ],
          ['#markup' => $this->t(self::COLLECTIVE_TODO_SELECT_ERROR)],
          '#suffix' => '<br/>',
        ],

        'emails' => [
          '#type' => 'textarea',
          '#title' => $this->t('Invite people to join this Collective'),
          '#attributes' => [
            'size' => 34,
            'maxlength' => 2147483646,
            'placeholder' => 'john@smith.com, ncedi@shaya.com...',
            'name' => 'emails',
          ],
        ],
      ],
    ];

    $view = Views::getView('my_collectives');
    $view->setDisplay('select');
    $view->execute();

    if ($view->total_rows == 0){
      $tab['content']['collective_todo_reuse']['#access'] = FALSE;
      $tab['content']['collective_todo_new']
        ['#attributes']['checked'] = 'checked';
    } else foreach($view->result as $row){
      $collective = $row->_entity;
      $option = [
        '#type' => 'container',
        '#attributes' => ['class' => 'collective'],
        $collective->field_picture->first() ? [
          '#theme' => 'image_style',
          '#style_name' => 'tiny_25x25',
          '#uri' => $collective
            ->field_picture
            ->first()
            ->entity
            ->getFileUri(),
        ] : [],
        [
          '#markup' => $collective
            ->title
            ->value,
        ],
      ];

      $tab['content']
        ['existing_collective']
        ['collective']
        ['#options']
        [$row->nid] = render($option);

      $description = array_filter(
        [
          !CollectiveController::isAdmin($collective, $user, TRUE)
            ? : 'You need to be a group admin of this collective to register projects.'
        ]
      );

      if (!CollectiveController::setting($collective, 'projects')) {
        $tab['content']
        ['existing_collective']
        ['collective']
        [$row->nid] = [
          '#disabled' => TRUE,
          '#description' => $this->t(
            'This collective does not allow project registrations.'
          ),
          '#attributes' => [
            'class' => ['disabled'],
          ],
          '#weight' => 2,
        ];
      } else {
        if (!CollectiveController::isAdmin($collective, $user, TRUE)) {
          $tab['content']
          ['existing_collective']
          ['collective']
          [$row->nid] = [
            '#disabled' => TRUE,
            '#description' => $this->t(
              'You need to be a group admin of this collective to register projects.'
            ),
            '#attributes' => [
              'class' => ['disabled'],
            ],
            '#weight' => 2,
          ];
        }
      }
    }

    return $tab;
  }


  /* --- Go --- */


  /**
   * Go step builder
   */
  private function go($form_state){

    $module_path = \Drupal::service('module_handler')
      ->getModule('afrikaburn_registration')
      ->getPath();

    $tab = [
      '#title' => 'Go',
      '#group_name' => 'go',
      '#bundle' => 'collective',
      '#type' => 'details',
      '#open' => TRUE,
      '#attributes' => ['id' => 'go', 'class' => ['field-group-tab']],
      'content' => [
        '#prefix' => '
        <h3>When you click "<strong>Go</strong>", this wizard will:</h3>
        <div class="form-item existing-collectives"><div class="description">',

        'intro' => ['#markup' => '<ul>'],

        'new-collective' => [
          '#type' => 'container',
          ['#markup' => '
            <li>Create a new Collecive, located in the left sidebar under the
              collectives heading:
              <div class="help-image">
                  <img src="' . $module_path . '/images/help-collective.png">
              </div>
            </li>'
          ],
          '#states' => [
            'visible' => [
              ':input[name="collective_todo"]' => ['value' => 'new']
            ],
          ],
        ],

        'new-project' => [
          '#type' => 'container',
          [
            '#markup' => '
              <li>Create a new <strong>draft registration </strong> located on
                your Collective page in the right hand column under the Projects
                heading:

                <div class="help-image">
                    <img src="' . $module_path . '/images/help-project.png">
                </div>

                <strong>You still have to complete and submit it!</strong>.
                Each section will turn green as you complete it.
              </li>'
          ],
          '#states' => [
            'visible' => [':input[name="project_todo"]' => ['value' => 'new']],
          ],
        ],

        'existing-project' => [
          '#type' => 'container',
          ['#markup' => '<li>Create a <strong>draft</strong> copy of an exising
                             registration <strong>(You still have to review and
                             submit it)</strong></li>'],
          '#states' => [
            'visible' => [
              ':input[name="project_todo"]' => ['value' => 'existing'],
            ],
          ],
        ],

        'existing-collective' => [
          '#type' => 'container',
          ['#markup' => '<li>Using an exising Collective.</li>'],
          '#states' => [
            'visible' => [
              ':input[name="collective_todo"]' => ['value' => 'existing'],
            ],
          ],
        ],

        'invitations' => [
          '#type' => 'container',
          ['#markup' => '<li>Invite some people to join the Collective.</li>'],
          '#states' => [
            'visible' => ['#edit-emails' => ['filled' => TRUE]],
          ],
        ],

        'outro' => ['#markup' => '</ul>'],

        '#suffix' => '</div></div>',
      ],
    ];

    return $tab;
  }
}