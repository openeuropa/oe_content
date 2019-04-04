<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\LinkitMatcherPlugin;

use Drupal\Core\Entity\EntityInterface;
use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;

/**
 * Provides overriden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends NodeMatcher {

  protected function buildPath(EntityInterface $entity) {
    return $entity->toUrl('purl', ['path_processing' => FALSE])->toString();
    //return '/content/' . $entity->uuid();
  }

//  protected function createSuggestion(EntityInterface $entity) {
//
//  }

}
