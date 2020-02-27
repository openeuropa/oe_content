<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface for content entity wrappers.
 */
interface EntityWrapperInterface {

  /**
   * Get wrapped entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   Wrapped entity.
   */
  public function getEntity(): ContentEntityInterface;

  /**
   * Get wrapped entity ID machine name.
   *
   * @return string
   *   Wrapped entity ID machine name.
   */
  public function getEntityId(): string;

  /**
   * Get wrapped entity bundle machine name, of the entity machine name if none.
   *
   * @return string
   *   Wrapped entity bundle machine name.
   */
  public function getEntityBundle(): string;

  /**
   * Factory method, returns an entity wrapper given an entity object.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity object.
   *
   * @return \Drupal\oe_content\EntityWrapperInterface
   *   Entity wrapper.
   */
  public static function getInstance(ContentEntityInterface $entity): EntityWrapperInterface;

}
