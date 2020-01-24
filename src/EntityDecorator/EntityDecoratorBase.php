<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EntityDecorator;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Base class for content entity decorators.
 *
 * To simplify magic method accessibility checks (see below) extending classes
 * must be set as 'final'.
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
   * Passes through all unknown properties onto the decorated object.
   *
   * @param string $property
   *   The property to call on the decorated object.
   *
   * @return mixed
   *   The property value.
   */
  public function __get($property) {
    $reflection = new \ReflectionProperty($this->entity, $property);
    if (!$reflection->isPublic()) {
      $class_name = get_class($this->entity);
      throw new \BadMethodCallException("Property {$class_name}->{$property} is not public. You can only access public properties on decorated objects.");
    }
    return $this->entity->{$property};
  }

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
    $reflection = new \ReflectionMethod($this->entity, $method);
    if (!$reflection->isPublic()) {
      $class_name = get_class($this->entity);
      throw new \BadMethodCallException("Method {$class_name}::{$method} is not public. You can only access public methods on decorated objects.");
    }
    return call_user_func_array([$this->entity, $method], $args);
  }

}
