<?php

namespace Drupal\oe_content\EventSubscriber;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Sets the _admin_route for specific RDF entity routes.
 */
class RdfAdminRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Make the RDF entity management routes use the admin theme.
    $rdf_routes = [
      'rdf_entity.rdf_add_page',
      'rdf_entity.rdf_add',
      'entity.rdf_entity.edit_form',
      'entity.rdf_entity.delete_form',
    ];
    foreach ($collection->all() as $name => $route) {
      if (in_array($name, $rdf_routes)) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

}
