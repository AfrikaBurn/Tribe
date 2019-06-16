<?php
/**
 * @file: view.inc
 * Contains collective view utilities.
 */


use \Drupal\Component\Utility\UrlHelper;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;
use \Drupal\user\Entity\User;


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


/* --- Links --- */


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

    $user = User::load(\Drupal::currentUser()->id());
    $public = CollectiveController::setting($build['#node'], 'public');
    $public_members = CollectiveController::setting($build['#node'], 'public_members');
    $private_members = CollectiveController::setting($build['#node'], 'private_members');
    $exposure = _exposure($public, $vetted, $public_members, $private_members, $open);

    $link = $exposure
      ? '<a href="/collective/' . $nid . '/join/' . $uid . '">Join anyway</a>'
      : '<a href="/collective/' . $nid . '/join/' . $uid . '" class="join">Join</a>';

    $element = [
      'prefix' => $exposure ? '<details><summary class="join">Join</summary><section>' : '',
      'exposing' => $exposure,
      'confirm' => $exposure ? '<p class="links">' .
        $link .
        '<a href="' . \Drupal::request()->getRequestUri() . '" class="cancel">No thanks</a>
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

  $open = CollectiveController::setting($build['#node'], 'open');
  $vetted = CollectiveController::setting($build['#node'], 'vetted');

  if (
    $display->getComponent('request') &&
    !$is_member && $open && $vetted
  ){

    $user = User::load(\Drupal::currentUser()->id());
    $public = CollectiveController::setting($build['#node'], 'public');
    $public_members = CollectiveController::setting($build['#node'], 'public_members');
    $private_members = CollectiveController::setting($build['#node'], 'private_members');
    $exposure = _exposure($public, $vetted, $public_members, $private_members, $open);
    $requested = NULL !== CollectiveController::isRequested($build['#node'], $user);

    $link = $requested
      ? ($exposure
        ? '<a href="/collective/' . $nid . '/withdraw/' . $uid . '">Withdraw request</a>'
        : '<a href="/collective/' . $nid . '/withdraw/' . $uid . '" class="withdraw">Withdraw request</a>'
      )
      : ($exposure
        ? '<a href="/collective/' . $nid . '/request/' . $uid . '">Request anyway</a>'
        : '<a href="/collective/' . $nid . '/request/' . $uid . '" class="request">Request to join</a>'
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
            '<a href="' . \Drupal::request()->getRequestUri() . '" class="cancel">' .
              ($requested ? 'Ok then' : 'No thanks') .
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

  $user = User::load(\Drupal::currentUser()->id());
  $open = CollectiveController::setting($build['#node'], 'open');
  $vetted = CollectiveController::setting($build['#node'], 'vetted');
  $invited = CollectiveController::isInvited($build['#node'], $user);

  if (
    $display->getComponent('accept') &&
    !$is_member && $invited
  ){

    $user = User::load(\Drupal::currentUser()->id());
    $public = CollectiveController::setting($build['#node'], 'public');
    $public_members = CollectiveController::setting($build['#node'], 'public_members');
    $private_members = CollectiveController::setting($build['#node'], 'private_members');
    $exposure = _exposure($public, $vetted, $public_members, $private_members, $open, $invited);

    $link = $exposure
      ? '<a href="/collective/' . $nid . '/accept/' . $uid . '">Accept anyway</a>'
      : '<a href="/collective/' . $nid . '/accept/' . $uid . '" class="accept">Accept</a>';

    $element = [
      'prefix' => $exposure ? '<details><summary class="accept">Accept invitation</summary><section>' : '',
      'exposing' => $exposure,
      'confirm' => $exposure ? '<p class="links">' .
        $link .
        '<a href="' . \Drupal::request()->getRequestUri() . '" class="cancel">Close</a>
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

  $user = User::load(\Drupal::currentUser()->id());
  $invited = CollectiveController::isInvited($build['#node'], $user);

  if (
    $display->getComponent('ignore') &&
    !$is_member && $invited
  ){

    $link = '<a href="/collective/' . $nid . '/ignore/' . $uid . '" class="ignore">Ignore</a>';

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

    $build['leave'] = [
      '#markup' => '<details>
        <summary class="leave">Leave</summary>
        <section>
          <p class="links">
            <a href="/collective/' . $nid . '/leave/' . $uid . '">YES, let me out!</a>
            <a href="' . \Drupal::request()->getRequestUri() . '" class="cancel">Never mind</a>
            </p>
          <section>
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


/* --- Membership links --- */


/**
 * Builds user admin links
 */
function _user_admin_links(&$variables){

  $user = User::load(\Drupal::currentUser()->id());
  $member = $variables['row']->_relationship_entities['uid'];
  $collective = \Drupal::entityTypeManager()->getStorage('node')->load(
    $variables['view']->args[0]
  );

  if ($collective){
    $variables['fields']['admin']['content'] = _build_admin(
      $user,
      $member,
      $collective
    );
    $variables['fields']['ban']['content'] = _build_ban(
      $user,
      $member,
      $collective
    );
    $variables['fields']['boot']['content'] = _build_boot(
      $user,
      $member,
      $collective
    );
    $variables['fields']['strip']['content'] = _build_strip(
      $user,
      $member,
      $collective
    );
  }
}

/**
 * Builds an admin link
 */
function _build_admin($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    !CollectiveController::isAdmin($collective, $member)
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
 * Builds a ban link
 */
function _build_ban($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user)
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
 * Builds a boot link
 */
function _build_boot($user, $member, $collective){
  if (CollectiveController::isAdmin($collective, $user)){
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
 * Builds a strip link
 */
function _build_strip($user, $member, $collective){
  if (
    CollectiveController::isAdmin($collective, $user) &&
    CollectiveController::isAdmin($collective, $member)
  ){
    return [
      '#markup' =>
        '<a class="admin admin-strip" href="/collective/' .
        $collective->id() .
        '/strip/' .
        $member->id() .
        '">Strip admin privileges</a>',
    ];
  }
}


/* --- Link utilities --- */


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
  $exposure = @array_filter(
    [
      $public_members || $private_members
        ? 'Your <strong>picture, username, first</strong> and <strong>last name</strong> will be visible to '
        : FALSE,
      $public_members || $private_members && $open &&!$vetted ? t('<strong>anyone with a Bio</strong>') : FALSE,
      $private_members && ($open || $invited) && $vetted ? t('<strong>other members of the collective</strong>') : FALSE,
    ]
  );

  return @implode(
    array_filter(
      [
        $exposing
          ? '<p>' . array_shift($exposing) . implode(' and ', $exposing) . '.</p>'
          : FALSE,
        $exposure
          ? '<p>' . array_shift($exposure) . implode(' and ', $exposure) . '.</p>'
          : FALSE
      ]
    )
  );
}