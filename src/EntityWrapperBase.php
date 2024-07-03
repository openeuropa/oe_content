<?php

declare(strict_types=1);

namespace Drupal\oe_content;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Base class for content entity wrappers.
 */
abstract class EntityWrapperBase implements EntityWrapperInterface {

  /**
   * Wrapped entity object.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * EntityWrapperBase constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Wrapped entity object.
   */
  public function __construct(ContentEntityInterface $entity) {
    // Ensure entity is supported by the current wrapper.
    if ($entity->getEntityTypeId() !== $this->getEntityId()) {
      throw new \InvalidArgumentException("The current wrapper only accepts '{$this->getEntityId()}' entities.");
    }

    // If entity type supports bundle then ensure it's supported by the wrapper.
    if ($entity->getEntityType()->hasKey('bundle') && $entity->bundle() !== $this->getEntityBundle()) {
      throw new \InvalidArgumentException("The current wrapper only accepts '{$this->getEntityId()}' entities of type '{$this->getEntityBundle()}'.");
    }
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(): ContentEntityInterface {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function getInstance(ContentEntityInterface $entity): EntityWrapperInterface {
    return new static($entity);
  }

}
