<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\field\FieldConfigInterface;

/**
 * Manager class for composite reference fields.
 *
 * @package Drupal\oe_content
 */
class CompositeReferenceFieldManager implements CompositeReferenceFieldManagerInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CompositeReferenceFieldManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferencingEntities(EntityInterface $entity): array {
    $referencing_entities = [];
    // Getting all fields which could have references to the given entity.
    $fields = $this->entityTypeManager->getStorage('field_config')->loadByProperties([
      'field_type' => 'entity_reference',
    ]);
    $fields = array_merge($fields, $this->entityTypeManager->getStorage('field_config')->loadByProperties([
      'field_type' => 'entity_reference_revisions',
    ]));

    // Only check fields that handle the given entity type.
    $field_referenced_to_entity = [];
    /** @var \Drupal\field\FieldConfigInterface $field */
    foreach ($fields as $field) {
      $field_settings = $field->getSettings();
      if ($field_settings['handler'] === 'default:' . $entity->getEntityTypeId()) {
        $field_referenced_to_entity[$field->getTargetEntityTypeId()][] = $field->getName();
      }
    }

    // Load all entities that have a reference to the given entity.
    foreach ($field_referenced_to_entity as $entity_type_id => $field_names) {
      $query = $this->entityTypeManager->getStorage($entity_type_id)->getQuery('OR');
      foreach ($field_names as $field_name) {
        $query->condition($field_name, $entity->id());
      }
      $ids = $query->execute();
      if ($ids) {
        $referencing_entities = array_merge($referencing_entities, $ids);
      }
    }
    return $referencing_entities;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteCompositeReferences(EntityInterface $entity, FieldDefinitionInterface $field_definition): void {
    if ($field_definition instanceof FieldConfigInterface && $field_definition->getThirdPartySetting('oe_content', 'composite', FALSE)) {
      $referenced_entities = $entity->get($field_definition->getName())->referencedEntities();
      /** @var \Drupal\Core\Entity\EntityInterface $referenced_entity */
      foreach ($referenced_entities as $referenced_entity) {
        if ($referenced_entity->id() !== $entity->id() && empty($this->getReferencingEntities($referenced_entity))) {
          $referenced_entity->delete();
        }
      }
    }
  }

}
