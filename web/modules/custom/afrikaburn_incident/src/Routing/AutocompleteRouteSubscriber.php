<?php

namespace Drupal\afrikaburn_incident\Routing;
use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
class AutocompleteRouteSubscriber extends RouteSubscriberBase {
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('system.entity_autocomplete')) {
      $route->setDefault('_controller', '\Drupal\afrikaburn_incident\Controller\EntityAutocompleteController::handleAutocomplete');
    }
  }
}