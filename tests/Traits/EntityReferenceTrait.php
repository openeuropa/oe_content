<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

/**
 * Helper trait to handle entity revision fields in Behat tests.
 */
trait EntityReferenceTrait {

  /**
   * Get revision field in a multi-value, parsable format.
   *
   * @param string $field_name
   *   Revision field name.
   * @param string $entity_type
   *   Entity type machine name.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Expanded field name with comma separated list of target IDs.
   */
  protected function getRevisionField(string $field_name, string $entity_type, string $labels): array {
    // Transform titles to ids and maintain the comma separated format.
    $items = explode(',', $labels);
    $items = array_map('trim', $items);
    $ids = [];
    foreach ($items as $item) {
      $ids[] = $this->loadEntityByLabel($entity_type, $item)->id();
    }

    return [
      "{$field_name}:target_id" => implode(',', $ids),
    ];
  }

}
