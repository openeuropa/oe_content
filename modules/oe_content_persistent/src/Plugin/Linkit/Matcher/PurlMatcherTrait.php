<?php

declare(strict_types=1);

namespace Drupal\oe_content_persistent\Plugin\Linkit\Matcher;

use Drupal\Core\Entity\EntityInterface;
use Drupal\linkit\Suggestion\EntitySuggestion;

/**
 * Provides helper methods for matchers to use persistent URLs.
 */
trait PurlMatcherTrait {

  /**
   * The config of PURL.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * {@inheritdoc}
   */
  protected function buildPath(EntityInterface $entity) {
    return $this->config->get('base_url') . $entity->uuid();
  }

  /**
   * {@inheritdoc}
   */
  protected function createSuggestion(EntityInterface $entity) {
    $suggestion = new EntitySuggestion();
    $suggestion->setLabel($this->buildLabel($entity))
      ->setGroup($this->buildGroup($entity))
      ->setDescription($this->buildDescription($entity))
      ->setPath($this->buildPath($entity));
    return $suggestion;
  }

}
