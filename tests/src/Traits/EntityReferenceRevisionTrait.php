<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Helper trait to handle entity reference revision fields in Behat tests.
 */
trait EntityReferenceRevisionTrait {

  /**
   * Get reference revision field in a multi-value, parsable format.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   Entity type machine name.
   * @param string $field_name
   *   Reference revision field name.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Pair of target_id and target_revision_id for given field.
   */
  protected function getReferenceRevisionField(string $entity_type, string $bundle, string $field_name, string $labels): array {
    $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
    $configuration = \Drupal::service('plugin.manager.entity_reference_selection')->getSelectionHandler($field_config)->getConfiguration();
    $target_entity_type_id = \Drupal::entityTypeManager()->getDefinition($configuration['target_type'])->id();

    // Transform titles to ids and maintain the comma separated format.
    $items = explode(',', $labels);
    $items = array_map('trim', $items);
    $ids = [];
    $revision_ids = [];
    foreach ($items as $item) {
      if ($target_entity_type_id === 'skos_concept') {
        $entity = $this->loadSkosConceptEntityByLabel($item, $configuration['concept_schemes']);
      }
      else {
        $entity = $this->loadEntityByLabel($target_entity_type_id, $item, $configuration['target_bundles']);
      }
      $ids[] = $entity->id();
      $revision_ids[] = $entity->getRevisionId();
    }

    // For revision reference field we have give the target_revision_id.
    return [
      "{$field_name}:target_id" => implode(',', $ids),
      "{$field_name}:target_revision_id" => implode(',', $revision_ids),
    ];
  }

  /**
   * Creates a field of an entity reference field storage on the bundle.
   *
   * @param string $entity_type
   *   The type of entity the field will be attached to.
   * @param string $bundle
   *   The bundle name of the entity the field will be attached to.
   * @param string $field_name
   *   The name of the field; if it already exists, a new instance of
   *   the existing field will be created.
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
   *
   * @see \Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection::buildConfigurationForm()
   */
  protected function createEntityReferenceRevisionField(string $entity_type, string $bundle, string $field_name, string $field_label, string $target_entity_type, string $selection_handler = 'default', array $selection_handler_settings = [], int $cardinality = 1) {
    // Look for or add the specified field to the requested entity bundle.
    if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
      FieldStorageConfig::create([
        'field_name' => $field_name,
        'type' => 'entity_reference_revision',
        'entity_type' => $entity_type,
        'cardinality' => $cardinality,
        'settings' => [
          'target_type' => $target_entity_type,
        ],
      ])->save();
    }
    if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
      FieldConfig::create([
        'field_name' => $field_name,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'label' => $field_label,
        'settings' => [
          'handler' => $selection_handler,
          'handler_settings' => $selection_handler_settings,
        ],
      ])->save();
    }
  }

}
