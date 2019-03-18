<?php
/**
 * @file: view.inc
 * Contains collective view utilities.
 */


use \Drupal\Component\Utility\UrlHelper;


/**
 * Builds a post listing view
 */
function _build_post_view(&$build, $display, $uid, $nid, $is_admin, $is_member){

  if (
    $display->getComponent('posts') &&
    ($is_member || !afrikaburn_collective_setting('private_discussion', $nid))
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
    ($is_member || !afrikaburn_collective_setting('privileged_discussion', $nid))
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

/**
 * Builds a join link
 */
function _build_join(&$build, $display, $uid, $nid, $is_admin, $is_member){

  if ($display->getComponent('join') && !$is_member && afrikaburn_collective_setting('open', $nid)){

    $build['join'] = [
      '#markup' => afrikaburn_collective_setting('vetted', $nid)
        ? (afrikaburn_collective_requested()
            ? '<a class="join requested">Membership requested</a>'
            : '<a class="join" href="/collective/' . $nid . '/join">Request to Join</a>'
          )
        : '<a class="join" href="/collective/' . $nid . '/join">Join</a>'
    ];
  }
}

/**
 * Builds a leave link
 */
function _build_leave(&$build, $display, $uid, $nid, $is_admin, $is_member){

  if ($display->getComponent('leave') && $is_member){

    $build['leave'] = [
      '#markup' => '<details class="leave-collective">
        <summary>Leave</summary>
        <a href="/collective/' . $nid . '/leave">
          YES, let me out!
        </a>
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
        '/edit/edit">Edit</a>',
    ];
  }
}

