<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;

/**
 * Provides common functionality for the composite reference test classes.
 */
trait CompositeReferenceTestTrait {

  /**
   * Creates an entity reference field on the specified bundle.
   *
   * @param string $entity_type
   *   The type of entity the field will be attached to.
   * @param string $bundle
   *   The bundle name of the entity the field will be attached to.
   * @param string $field_name
   *   The name of the field; if it already exists,
   *   a new instance of the existing field will be created.
   * @param string $field_label
   *   The label of the field.
   * @param string $target_entity_type
   *   The type of the referenced entity.
   * @param string $selection_handler
   *   The selection handler used by this field.
   * @param array $selection_handler_settings
   *   An array of settings supported by the selection handler specified above.
   *   (e.g. 'target_bundles', 'sort', 'auto_create', etc).
   * @param int $cardinality
   *   The cardinality of the field.
   * @param bool $revision
   *   Whether to make it a entity reference revision field or not.
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The field_config for the newly created field.
   */
  protected function createEntityReferenceField(string $entity_type, string $bundle, string $field_name, string $field_label, string $target_entity_type, string $selection_handler = 'default', array $selection_handler_settings = [], int $cardinality = 1, bool $revision = FALSE): FieldConfigInterface {
    // Look for or add the specified field to the requested entity bundle.
    if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
      /** @var \Drupal\field\FieldStorageConfigInterface $reference_field_storage */
      $reference_field_storage = FieldStorageConfig::create([
        'field_name' => $field_name,
        'type' => 'entity_reference',
        'entity_type' => $entity_type,
        'cardinality' => $cardinality,
        'settings' => [
          'target_type' => $target_entity_type,
        ],
      ]);
      if ($revision) {
        $reference_field_storage->set('type', 'entity_reference_revisions');
      }
      $reference_field_storage->save();
    }
    if ($reference_field = FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
      return $reference_field;
    }
    $reference_field = FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $field_label,
      'settings' => [
        'handler' => $selection_handler,
        'handler_settings' => $selection_handler_settings,
      ],
    ]);
    $reference_field->save();

    return $reference_field;
  }

}
