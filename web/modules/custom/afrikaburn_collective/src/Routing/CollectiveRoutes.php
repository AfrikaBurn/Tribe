<?php

/**
 * @file
 * Contains Afrikaburn Collective Routes.
 */

namespace Drupal\afrikaburn_collective\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class CollectiveRoutes extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    // Node Edits
    if ($route = $collection->get('entity.node.edit_form')) {
      $route->setRequirement('_is_admin', 'TRUE');
    }

    // Node deletes
    if ($route = $collection->get('entity.node.delete_form')) {
      $route->setRequirement('_is_admin', 'TRUE');
    }

  }

}