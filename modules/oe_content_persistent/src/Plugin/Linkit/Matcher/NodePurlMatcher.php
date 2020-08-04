<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Linkit\Matcher;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\node\NodeInterface;

/**
 * Provides overriden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends PurlEntityMatcherBase {

  /**
   * {@inheritdoc}
   */
  protected function applyPublishedCondition(QueryInterface &$query) {
    if ($this->configuration['include_unpublished'] == FALSE) {
      $query->condition('status', NodeInterface::PUBLISHED);
    }
    elseif (count($this->moduleHandler->getImplementations('node_grants')) === 0) {
      if (($this->currentUser->hasPermission('bypass node access') || $this->currentUser->hasPermission('view any unpublished content'))) {
        // User can see all content, no check necessary.
      }
      elseif ($this->currentUser->hasPermission('view own unpublished content')) {
        // Users with "view own unpublished content" can see only their own.
        if ($this->configuration['include_unpublished'] == TRUE) {
          $or_condition = $query
            ->orConditionGroup()
            ->condition('status', NodeInterface::PUBLISHED)
            ->condition('uid', $this->currentUser->id());
          $query->condition($or_condition);
        }
      }
    }
    else {
      // All other users should only get published results.
      $query->condition('status', NodeInterface::PUBLISHED);
    }
  }

}
