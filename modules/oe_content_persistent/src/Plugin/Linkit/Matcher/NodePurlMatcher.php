<?php

declare(strict_types=1);

namespace Drupal\oe_content_persistent\Plugin\Linkit\Matcher;

use Drupal\linkit\Plugin\Linkit\Matcher\NodeMatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides overridden linkit matchers for the node entity type.
 */
class NodePurlMatcher extends NodeMatcher {

  use PurlMatcherTrait;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->config = $instance->configFactory->get('oe_content_persistent.settings');
    return $instance;
  }

  /**
   * {@inheritdoc}
   *
   * This method overrides the parent method to revert the database escapeLike
   * for the user input to match the original URL.
   */
  public function findEntityIdByUrl($user_input, $base_url = '') {
    // Revert database escapeLike for the user input to match the original URL.
    $user_input = str_replace(['\\_'], ['_'], $user_input);
    return parent::findEntityIdByUrl($user_input, $base_url);
  }

}
