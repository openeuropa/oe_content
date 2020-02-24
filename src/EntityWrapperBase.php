<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Base class for content entity wrappers.
 */
abstract class EntityWrapperBase {

  /**
   * Wrapped entity object.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $entity;

  /**
   * EntityWrapperBase constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Wrapped entity object.
   */
  public function __construct(ContentEntityInterface $entity) {
    if ($entity->getEntityTypeId() !== $this->getWrappedEntityId() || $entity->bundle() !== $this->getWrappedEntityBundle()) {
      throw new \InvalidArgumentException("The current wrapper only accepts '{$this->getWrappedEntityId()}' of type '{$this->getWrappedEntityBundle()}'.");
    }
    $this->entity = $entity;
  }

  /**
   * Get wrapped entity ID machine name.
   *
   * @return string
   *   Wrapped entity ID machine name.
   */
  abstract protected function getWrappedEntityId(): string;

  /**
   * Get wrapped entity bundle machine name.
   *
   * @return string
   *   Wrapped entity bundle machine name.
   */
  abstract protected function getWrappedEntityBundle(): string;

}
