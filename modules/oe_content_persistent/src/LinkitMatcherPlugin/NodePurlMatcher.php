<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\LinkitMatcherPlugin;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;

/**
 * Provides overriden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends NodeMatcher {

  /**
   * Builds the path string used in the suggestion.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return string
   *   The path for this entity.
   */
  protected function buildPath(EntityInterface $entity) {
    // @Todo Implement purl link template.
    return Url::fromRoute('oe_content_persistent.redirect', ['uuid' => $entity->uuid()])->setAbsolute()->toString();
  }

}
