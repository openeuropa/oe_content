<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\LinkitMatcherPlugin;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;

/**
 * Provides overriden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends NodeMatcher {

  /**
   * {@inheritdoc}
   */
  protected function buildPath(EntityInterface $entity) {
    return Url::fromRoute('oe_content_persistent.redirect', ['uuid' => $entity->uuid()])->setAbsolute()->toString();
  }

}
