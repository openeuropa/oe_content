<?php

namespace Drupal\oe_content_persistent\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for entity purl links routes.
 */
class PurlRouteSubscriber extends RouteSubscriberBase {

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * PersistentUrlController constructor.
   *
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The service for transforming uuid to alias/system path.
   */
  public function __construct(ContentUuidResolverInterface $uuid_resolver) {
    $this->contentUuidResolver = $uuid_resolver;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->contentUuidResolver->getSupportedEntityTypes() as $entity_type_id => $entity_type) {

      if ($entity_type->hasLinkTemplate('purl')) {
        $route = new Route(
          $entity_type->getLinkTemplate('purl'),
          [
            '_controller' => '\Drupal\oe_content_persistent\Controller\PersistentUrlController::index',
            '_title' => 'Redirect in progress',
          ],
          [
            '_permission' => 'access content',
            //'uuid' => '[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}',
          ]
//          [
//            'parameters' => [
//              'uuid' => [
//                'type' => 'entity:uuid',
//              ],
//            ],
//          ]
        );
        $collection->add("entity.$entity_type_id.purl", $route);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[RoutingEvents::ALTER][] = 'onAlterRoutes';
    return $events;
  }

}
