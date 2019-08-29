<?php

/**
 * @file Contains afrikaburn collective post notification methods.
 */

use \Drupal\user\Entity\User;
use \Drupal\afrikaburn_collective\Controller\CollectiveController;

 /**
 * Notify collectives about posts in the collective
 */
function _notify_post($post, $meta_collective, $address_settings){

  if (CollectiveController::setting($meta_collective['collective'], 'emails')) {

    $user = User::load(\Drupal::currentUser()->id());

    _notify(
      'no-reply@afrikaburn.com',
      'AfrikaBurn ' . $meta_collective['title'], $address_settings->get('collective-address'),
      $meta_collective['members'] . ',' . $address_settings->get('archive'),
      $user->name->value . ' posted in ' . $meta_collective['title'],
      render(
        \Drupal::entityTypeManager()
          ->getViewBuilder('comment')
          ->view($post, 'mail')
      ),
      $post
    );
  }
}