<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content;

/**
 * Content storage for entities without labels.
 */
final class ContentStorage {

  /**
   * Singleton instance.
   *
   * @var \Drupal\Tests\oe_content\Behat\Content\ContentStorage
   */
  private static $instance = NULL;

  /**
   * List of paragraphs collected during scenario execution.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities = [];

  /**
   * Constructor.
   */
  public function __construct() {
    self::$instance = $this;
  }

  /**
   * Returns the class instance.
   *
   * A singleton is used because it is storage of all sub entities.
   *
   * @return \Drupal\Tests\oe_content\Behat\ContentStorage
   *   Class instance.
   */
  public static function getInstance(): ContentStorage {
    if (self::$instance === NULL) {
      self::$instance = new ContentStorage();
    }
    return self::$instance;
  }

  /**
   * Adds entity to the storage.
   *
   * @param string $name
   *   Fake entity label.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to be added to the storage.
   */
  public function addEntity(string $name, EntityInterface $entity): void {
    self::$instance->entities[$name] = $entity;
  }

  /**
   * Removes entity from the storage.
   *
   * @param string $name
   *   Fake entity label.
   */
  public function removeEntity(string $name): void {
    unset(self::$instance->entities[$name]);
  }

  /**
   * Gets entity by label.
   *
   * @param string $name
   *   Fake entity label.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   Content entity or NULL.
   */
  public function getEntity(string $name): ?EntityInterface {
    if (isset(self::$instance->entities[$name])) {
      return self::$instance->entities[$name];
    }

    return NULL;
  }

  /**
   * Gets list of entities from the storage.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   List of content entities.
   */
  public function getEntities(): array {
    return self::$instance->entities;
  }

}
