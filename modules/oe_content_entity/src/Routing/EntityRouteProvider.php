<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * We need to override canonical to serve edit page for corporate entities.
 *
 * These entities are not meant to have accessible canonical urls.
 */
class EntityRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getCanonicalRoute(EntityTypeInterface $entity_type) {
    return parent::getEditFormRoute($entity_type);
  }

}
