<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\field\Entity\FieldConfig;

/**
 * Helper trait to handle entity reference fields in Behat tests.
 */
trait EntityReferenceTrait {

  /**
   * Get reference field in a multi-value, parsable format.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   * @param string $field_name
   *   The field name.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Expanded field name with comma separated list of target IDs.
   */
  protected function getReferenceField(string $entity_type, string $bundle, string $field_name, string $labels): array {
    $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
    /** @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface $handler */
    $handler = \Drupal::service('plugin.manager.entity_reference_selection')->getSelectionHandler($field_config);
    // Transform titles to ids and maintain the comma separated format.
    $items = explode(',', $labels);
    $items = array_map('trim', $items);
    $ids = [];
    foreach ($items as $item) {
      $found_entities = $handler->getReferenceableEntities($item);
      $ids[] = key(reset($found_entities));
    }

    return [
      "{$field_name}:target_id" => implode(',', $ids),
    ];
  }

}
