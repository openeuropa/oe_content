<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\field\FieldConfigInterface;

/**
 * Helper trait to handle entity reference fields in Behat tests.
 */
trait EntityReferenceTrait {

  /**
   * Get reference field in a multi-value, parsable format.
   *
   * @param \Drupal\field\FieldConfigInterface $field_config
   *   Reference field config.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Expanded field name with comma separated list of target IDs.
   */
  protected function getReferenceField(FieldConfigInterface $field_config, string $labels): array {
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
      "{$field_config->getName()}:target_id" => implode(',', $ids),
    ];
  }

}
