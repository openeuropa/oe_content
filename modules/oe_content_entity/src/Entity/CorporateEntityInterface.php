<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity\Entity;

use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;

/**
 * Provides an interface for EntityBase class.
 *
 * @ingroup oe_content_entity
 */
interface CorporateEntityInterface extends EntityChangedInterface, EntityPublishedInterface, RevisionLogInterface {

  /**
   * Denotes that the entity is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the entity is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the corporate content entity type.
   *
   * @return string
   *   The type.
   */
  public function getType(): string;

  /**
   * Gets the corporate content entity name.
   *
   * @return string
   *   Name of the entity.
   */
  public function getName(): string;

  /**
   * Sets the corporate content entity name.
   *
   * @param string $name
   *   The name.
   *
   * @return \Drupal\oe_content_entity\Entity\CorporateEntityInterface
   *   The called corporate content entity.
   */
  public function setName(string $name): CorporateEntityInterface;

  /**
   * Gets the corporate content entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the corporate content entity.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the corporate content entity creation timestamp.
   *
   * @param int $timestamp
   *   The ecorporate content entity creation timestamp.
   *
   * @return \Drupal\oe_content_entity\Entity\CorporateEntityInterface
   *   The called corporate content entity.
   */
  public function setCreatedTime(int $timestamp): CorporateEntityInterface;

  /**
   * Gets the corporate content entity revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the corporate content entity revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\oe_content_entity\Entity\CorporateEntityInterface
   *   The called corporate content entity.
   */
  public function setRevisionCreationTime($timestamp);

}
