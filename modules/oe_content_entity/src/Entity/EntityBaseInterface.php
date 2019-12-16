<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity\Entity;

/**
 * Provides an interface for EntityBase class.
 *
 * @ingroup oe_content_entity
 */
interface EntityBaseInterface {

  /**
   * Gets the custom content entity type.
   *
   * @return string
   *   The type.
   */
  public function getType(): string;

  /**
   * Gets the custom content entity name.
   *
   * @return string
   *   Name of the entity.
   */
  public function getName(): string;

  /**
   * Sets the custom content entity name.
   *
   * @param string $name
   *   The name.
   *
   * @return \Drupal\oe_content_entity\Entity\EntityBaseInterface
   *   The called custom content entity.
   */
  public function setName(string $name): EntityBaseInterface;

  /**
   * Gets the custom content entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the custom content entity.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the custom content entity creation timestamp.
   *
   * @param int $timestamp
   *   The ecustom content entity creation timestamp.
   *
   * @return \Drupal\oe_content_entity\Entity\EntityBaseInterface
   *   The called custom content entity.
   */
  public function setCreatedTime(int $timestamp): EntityBaseInterface;

  /**
   * Gets the custom content entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the custom content entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\oe_content_entity\Entity\EntityBaseInterface
   *   The called custom content entity.
   */
  public function setRevisionCreationTime($timestamp);

}
