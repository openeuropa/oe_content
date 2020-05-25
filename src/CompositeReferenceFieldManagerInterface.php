<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Defines composite reference field managers.
 *
 * @package Drupal\oe_content
 */
interface CompositeReferenceFieldManagerInterface {

  /**
   * Returns a list of possible entities that reference the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The referenced entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   An array of entities that reference the given entity.
   */
  public function getReferencingEntities(EntityInterface $entity): array;

  /**
   * Reacts to the deletion of an entity and deletes its composite references.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The referencing entity.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to remove references from.
   */
  public function onDelete(EntityInterface $entity, FieldDefinitionInterface $field_definition): void;

}
