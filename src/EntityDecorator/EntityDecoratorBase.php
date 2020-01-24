<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EntityDecorator;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Base class for content entity decorators.
 */
abstract class EntityDecoratorBase {

  /**
   * Original entity object.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $entity;

  /**
   * EntityDecoratorBase constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Original entity object.
   */
  public function __construct(ContentEntityInterface $entity) {
    if ($entity->getEntityTypeId() !== $this->getDecoratedEntityId() || $entity->bundle() !== $this->getDecoratedEntityBundle()) {
      throw new \InvalidArgumentException("The current decorator only accepts '{$this->getDecoratedEntityId()}' of type '{$this->getDecoratedEntityBundle()}'.");
    }
    $this->entity = $entity;
  }

  /**
   * Get decorated entity ID machine name.
   *
   * @return string
   *   Decorated entity ID machine name.
   */
  abstract protected function getDecoratedEntityId(): string;

  /**
   * Get decorated entity bundle machine name.
   *
   * @return string
   *   Decorated entity bundle machine name.
   */
  abstract protected function getDecoratedEntityBundle(): string;

  /**
   * Passes through all unknown calls onto the decorated object.
   *
   * @param string $method
   *   The method to call on the decorated object.
   * @param array $args
   *   The arguments to send to the method.
   *
   * @return mixed
   *   The method result.
   */
  public function __call($method, array $args) {
    return call_user_func_array([$this->entity, $method], $args);
  }

}
