<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Interface for sub entity classes.
 *
 * @ingroup oe_content_sub_entity
 */
interface SubEntityInterface extends ContentEntityInterface, EntityChangedInterface, EntityPublishedInterface {

  /**
   * Denotes that the entity is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the entity is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the entity.
   */
  public function getCreatedTime(): int;

  /**
   * Sets the entity creation timestamp.
   *
   * @param int $timestamp
   *   The entity creation timestamp.
   *
   * @return \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   *   The called content entity.
   */
  public function setCreatedTime(int $timestamp): SubEntityInterface;

  /**
   * Gets the parent entity.
   *
   * Preserves language context with translated entities.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The parent entity.
   */
  public function getParentEntity(): ?ContentEntityInterface;

  /**
   * Set the parent entity.
   *
   * Entity reference revision fields will set parent entity information
   * automatically but, if we use a simple entity reference field type, then we
   * need to set them in the widget by using this method.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $parent
   *   The parent entity.
   * @param string $parent_field_name
   *   The parent field name.
   *
   * @return \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   *   Class instance.
   *
   * @see \Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem
   */
  public function setParentEntity(ContentEntityInterface $parent, string $parent_field_name): SubEntityInterface;

}
