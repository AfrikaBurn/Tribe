<?php
/**
 * @file: view.inc
 * Contains collective view utilities.
 */


use \Drupal\Component\Utility\UrlHelper;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\user\Entity\User;
use \Drupal\afrikaburn_collective\Utils;


/* --- Comments  --- */


function _reply_form(&$variables){

  $row = $variables['row'];
  $cid = $row->_entity->get('cid')->value;
  $nid = $variables['view']->args[0];

  $form =  \Drupal::service('entity.form_builder')->getForm(
    \Drupal::entityTypeManager()->getStorage('comment')->create(
      [
        'entity_type'  => 'node',
        'entity_id'    => $nid,
        'field_name'   => 'field_col_posts',
        'comment_type' => 'comment',
        'pid' => $cid,
      ]
    )
  );
  unset($form['comment_body']['widget'][0]['value']['#title']);
  $form['pid']['#value'] = $cid;

  $variables['fields']['replyto_comment']['content'] = render($form);
}


/* --- Posts --- */


/**
 * Builds a post listing view
 */
function _build_post_view(&$build, $display, $uid, $nid, $is_admin, $is_member){
  if (
    $display->getComponent('posts') &&
    ($is_member || !CollectiveController::setting($build['#node'], 'private_discussion'))
  ){
    $build['posts'] = views_embed_view('collective_posts', 'posts', $nid);
  }
}

/**
 * Builds a new post form
 */
function _build_post_form(&$build, $display, $uid, $nid, $is_admin, $is_member){

  if (
    $display->getComponent('post_something') &&
    ($is_member || !CollectiveController::setting($build['#node'], 'privileged_discussion'))
  ){

    $form = \Drupal::service('entity.form_builder')->getForm(
      \Drupal::entityTypeManager()->getStorage('comment')->create(
        [
          'entity_type'  => 'node',
          'entity_id'    => $nid,
          'field_name'   => 'field_col_posts',
          'comment_type' => 'post',
          'pid' => NULL,
        ]
      )
    );

    unset($form['comment_body']['widget'][0]['value']['#title']);
    $form['actions']['submit']['#value'] = t('Post it!');

    $form['#action'] .= '?destination=' .
      UrlHelper::encodePath(
        drupal_get_destination()['destination']
      );

    $build['post_something'] = [
      '#type' => 'markup',
      '#markup' => render($form),
      '#prefix' => '<details class="comment-form"><summary class="button">Post something</summary>',
      '#suffix' => '</details>',
    ];
  }
}


/* --- Collective links --- */


/**
 * Builds a join link
 */
function _build_join(&$build, $display, $uid, $nid, $is_admin, $is_member){

  $open = CollectiveController::setting($build['#node'], 'open');
  $vetted = CollectiveController::setting($build['#node'], 'vetted');

  if (
    $display->getComponent('join') &&
    !$is_member && $open && !$vetted
  ){

    $user = Utils::currentUser();
    $public = CollectiveController::setting($build['#node'], 'public');
    $public_members = CollectiveController::setting($build['#node'], 'public_members');
    $private_members = CollectiveController::setting($build['#node'], 'private_members');
    $exposure = _exposure($public, $vetted, $public_members, $private_members, $open);

    $link = $exposure
      ? _build_link('Join anyway', 'join', $nid, $uid, $exposure, FALSE)
      : _build_link('Join', 'join', $nid, $uid, $popup, FALSE);

    $element = [
      'prefix' => $exposure ? '<details><summary class="join">Join</summary><section>' : '',
      'exposing' => $exposure,
      'confirm' => $exposure ? '<p class="links">' .
        $link .
        '<a class="button cancel" href="' . \Drupal::request()->getRequestUri() . '">Nope</a>
      </p>' : $link,
      'suffix' => $exposure ? '</section></details>' : '',
    ];

    $build['join'] = [
      '#markup' => implode($element)
    ];
  }
}

/**
 * Builds a join request link
 */
function _build_request(&$build, $display, $uid, $nid, $is_admin, $is_member){

  $user = Utils::currentUser();
  $open = CollectiveController::setting($build['#node'], 'open');
  $vetted = CollectiveController::setting($build['#node'], 'vetted');
  $invited = CollectiveController::isInvited($build['#node'], $user);

  if (
    $display->getComponent('request') &&
    !$is_member && !$invited && $open && $vetted
  ){

    $public = CollectiveController::setting($build['#node'], 'public');
    $public_members = CollectiveController::setting($build['#node'], 'public_members');
    $private_members = CollectiveController::setting($build['#node'], 'private_members');
    $exposure = _exposure($public, $vetted, $public_members, $private_members, $open);
    $requested = NULL !== CollectiveController::isRequested($build['#node'], $user);

    $link = $requested
      ? _build_link('Withdraw request', 'withdraw', $nid, $uid, $exposure)
      : ($exposure
        ? _build_link('Request anyway', 'request', $nid, $uid, $exposure, FALSE)
        : _build_link('Request to join', 'withdraw', $nid, $uid, $exposure, FALSE)
      );

    $element = [
      'prefix' => $exposure
        ? $requested
          ? '<details><summary class="withdraw">Requested</summary><section>'
          : '<details><summary class="request">Request to join</summary><section>'
        : '',
      'exposing' => $exposure,
      'confirm' => $exposure
        ? '<p class="links">' . $link .
            '<a class="button cancel" href="' . \Drupal::request()->getRequestUri() . '">' .
              ($requested ? 'Ok then' : 'Nope') .
            '</a>' .
          '</p>'
        : $link,
      'suffix' => $exposure ? '</section></details>' : '',
    ];

    $build['request'] = [
      '#markup' => implode($element)
    ];
  }
}

/**
 * Builds a accept link
 */
function _build_accept(&$build, $display, $uid, $nid, $is_admin, $is_member){

  $user = Utils::currentUser();
  $open = CollectiveController::setting($build['#node'], 'open');
  $vetted = CollectiveController::setting($build['#node'], 'vetted');
  $invited = CollectiveController::isInvited($build['#node'], $user);

  if (
    $display->getComponent('accept') &&
    !$is_member && $invited
  ){

    $user = Utils::currentUser();
    $public = CollectiveController::setting($build['#node'], 'public');
    $public_members = CollectiveController::setting($build['#node'], 'public_members');
    $private_members = CollectiveController::setting($build['#node'], 'private_members');
    $exposure = _exposure($public, $vetted, $public_members, $private_members, $open, $invited);
    $destination = '?destination=' . \Drupal::destination()->get();

    $link = $exposure
      ? '<a class="button" href="/collective/' . $nid . '/accept/' . $uid . $destination . '">Accept anyway</a>'
      : '<a href="/collective/' . $nid . '/accept/' . $uid . $destination . '" class="accept">Accept</a>';

    $element = [
      'prefix' => $exposure ? '<details><summary class="accept" title="Accept the invitation to join this collective">Accept</summary><section>' : '',
      'exposing' => $exposure,
      'confirm' => $exposure ? '<p class="links">' .
        $link .
        '<a class="button cancel" href="' . \Drupal::request()->getRequestUri() . '">Close</a>
      </p>' : $link,
      'suffix' => $exposure ? '</section></details>' : '',
    ];

    $build['accept'] = [
      '#markup' => implode($element)
    ];
  }
}

/**
 * Builds an ignore link
 */
function _build_ignore(&$build, $display, $uid, $nid, $is_admin, $is_member){

  $user = Utils::currentUser();
  $invited = CollectiveController::isInvited($build['#node'], $user);

  if (
    $display->getComponent('ignore') &&
    !$is_member && $invited
  ){

    $destination = '?destination=' . \Drupal::destination()->get();
    $link = '<a href="/collective/' . $nid . '/ignore/' . $uid . $destination . '" class="ignore" title="Ignore the invitation to join this collective">Ignore</a>';

    $build['ignore'] = [
      '#markup' => $link
    ];
  }
}

/**
 * Builds a leave link
 */
function _build_leave(&$build, $display, $uid, $nid, $is_admin, $is_member){

  if ($display->getComponent('leave') && $is_member){

    $destination = '?destination=' . \Drupal::destination()->get();

    $build['leave'] = [
      '#markup' => '<details>
        <summary class="leave" title="Leave this collective">Leave</summary>
        <section>
          <p>Are you sure?</p>
          <p class="links">
            <a class="button" href="/collective/' . $nid . '/leave/' . $uid . $destination . '">Let me out!</a>
            <a class="button cancel" href="' . \Drupal::request()->getRequestUri() . '">Never mind</a>
          </p>
        </section>
      </details>',
    ];
  }
}

/**
 * Builds an edit link
 */
function _build_edit(&$build, $display, $uid, $nid, $is_admin, $is_member){
  if ($display->getComponent('edit') && $is_admin){
    $build['edit'] = [
      '#markup' =>
        '<a class="edit" title="Edit this Collective" href="/node/' .
        $nid .
        '/edit">Edit</a>',
    ];
  }
}


/* --- Indicators --- */


/**
 * Builds an initation indicator
 */
function _build_invited(&$build, $display, $uid, $nid, $is_admin, $is_member){

  $user = Utils::currentUser();
  $invited = CollectiveController::isInvited($build['#node'], $user);

  if (
    $display->getComponent('invited') &&
    !$is_member && $invited
  ){

    $build['invited'] = [
      '#markup' => '<div class="invitation">' . t('You\'ve been invited to join this collective!') . '</div>'
    ];
  }
}


/* --- Membership links --- */


/**
 * Builds user admin links
 */
function _user_admin_links(&$variables, $collective, $user, $member){

  if ($collective){

    foreach(
      [
        'admin', 'strip',
        'approve', 'reject',
        'ban', 'unban',
        'boot',
      ] as $action
    ) {

      $function = '_build_' . $action;
      $variables['fields'][$action]['content'] = $function(
        $user,
        $member,
        $collective
      );
    }
  }
}

/**
 * Builds an admin link
 */
function _build_admin($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    !CollectiveController::isAdmin($collective, $member, TRUE) &&
    CollectiveController::isMember($collective, $member, TRUE)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-admin" href="/collective/' .
        $collective->id() .
        '/admin/' .
        $member->id() .
        '">Promote to Admin</a>',
    ];
  }
}

/**
 * Builds a strip link
 */
function _build_strip($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    CollectiveController::isAdmin($collective, $member, TRUE) &&
    CollectiveController::isMember($collective, $member, TRUE)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-strip" href="/collective/' .
        $collective->id() .
        '/strip/' .
        $member->id() .
        '">Remove admin privileges</a>',
    ];
  }
}

/**
 * Builds a grant link
 */
function _build_approve($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    CollectiveController::isRequested($collective, $member)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-grant" href="/collective/' .
        $collective->id() .
        '/approve/' .
        $member->id() .
        '">Approve request</a>',
    ];
  }
}

/**
 * Builds a reject link
 */
function _build_reject($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    CollectiveController::isRequested($collective, $member)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-deny" href="/collective/' .
        $collective->id() .
        '/reject/' .
        $member->id() .
        '">Reject request</a>',
    ];
  }
}

/**
 * Builds a ban link
 */
function _build_ban($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    !CollectiveController::isBanned($collective, $member)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-ban" href="/collective/' .
        $collective->id() .
        '/ban/' .
        $member->id() .
        '">Ban from collective</a>',
    ];
  }
}

/**
 * Builds an unban link
 */
function _build_unban($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    CollectiveController::isBanned($collective, $member)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-unban" href="/collective/' .
        $collective->id() .
        '/unban/' .
        $member->id() .
        '">Lift ban</a>',
    ];
  }
}

/**
 * Builds a boot link
 */
function _build_boot($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    CollectiveController::isMember($collective, $member, TRUE)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-boot" href="/collective/' .
        $collective->id() .
        '/boot/' .
        $member->id() .
        '">Boot from collective</a>',
    ];
  }
}

/**
 * Builds a show name link
 */
function _build_show_name($user, $member, $collective){
  if (
    $member->id() == $user->id() &&
    !CollectiveController::isNameShown($collective, $member)
  ) {
    return [
      '#markup' =>
        '<a class="admin admin-show-name" href="/collective/' .
        $collective->id() .
        '/show-name/' .
        $member->id() .
        '">Show my real name here</a>',
    ];
  }
}

/**
 * Builds a hide name link
 */
function _build_hide_name($user, $member, $collective){
  if (
    $member->id() == $user->id() &&
    CollectiveController::isNameShown($collective, $member)
  ) {
    return [
      '#markup' =>
        '<a class="admin admin-hide-name" href="/collective/' .
        $collective->id() .
        '/hide-name/' .
        $member->id() .
        '">Hide my real name here</a>',
    ];
  }
}


/* --- Link utilities --- */


/**
 * Build a collective action link tag.
 * @param $label Link display label.
 * @param $action action part of the URL.
 * @param $nid Node ID of the collective.
 * @param $uid user ID of the action user.
 * @param $popup Whether this link is to be displayed in a popup.
 * @param $destination whether to create a destination param, defaults to TRUE.
 */
function _build_link($label, $action, $nid, $uid, $popup, $destination = TRUE){

  $url =
    '/collective/' . $nid . '/' . $action . '/' . $uid .
    ($destination ? '?destination=' . \Drupal::destination()->get() : '');
  $class = ' class="' . @implode(
    ' ',
    array_filter(
      [
        $popup ? 'button' : FALSE,
        !$popup ? $action : FALSE
      ]
    )
  ) . '"';

  return '<a href="' . $url . '"' . $class . '>' . $label . '</a>';
}


/**
 * Calculates a users' exposure should they join a collective.
 */
function _exposure($public, $vetted, $public_members, $private_members, $open, $invited = FALSE){

  $exposing = @array_filter(
    [
      $public_members || $private_members
        ? t('This collective allows ') : '',
      $public_members
        ? t('non-members to see who members are') : FALSE,
      $private_members
        ? t('members to see who other members are') : FALSE,
      $private_members && !$open
        ? t('membership requires approval') : FALSE,
      $private_members && $open &&!$vetted
        ? t('anyone with a Bio may join') : FALSE,
      $private_members && $open &&$vetted
        ? t('membership requires admin approval') : FALSE,
    ]
  );

  $exposed_to = @implode(' and ',
    array_filter(
      [
        $private_members && ($open || $invited) && $vetted ? t('<strong>the other members of the collective</strong>') : FALSE,
        t('<strong>admins of the collective</strong>'),
        $public_members || $private_members && $open &&!$vetted ? t('<strong>anyone with a Bio</strong>') : FALSE,
      ]
    )
  );

  return @implode(
    [
      $exposing
        ? '<p>' . array_shift($exposing) . implode(' and ', $exposing) . '.</p>'
        : FALSE,
      '<p>',
      'Your <strong>picture and username</strong> will be visible to ',
      $exposed_to,
      '</p>'
    ]
  );
}