<?php

/**
 * @file
 * Contains Afrikaburn Shared module.
 */

namespace Drupal\afrikaburn_shared\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RegistrationRoutes extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // dpm($collection);

    // Node views
    if ($route = $collection->get('entity.node.canonical')) {
      $route->setRequirement('_is_collective_member', 'TRUE');
    }

    // Node Edits
    if ($route = $collection->get('entity.node.edit_form')) {
      $route->setRequirement('_is_collective_admin', 'TRUE');
    }

    // Node deletes
    if ($route = $collection->get('entity.node.delete_form')) {
      $route->setRequirement('_is_collective_admin', 'TRUE');
    }

  }

}