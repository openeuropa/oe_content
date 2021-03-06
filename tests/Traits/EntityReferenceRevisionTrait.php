<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

/**
 * Helper trait to handle entity reference revision fields in Behat tests.
 */
trait EntityReferenceRevisionTrait {

  /**
   * Get reference revision field in a multi-value, parsable format.
   *
   * @param string $field_name
   *   Reference revision field name.
   * @param string $entity_type
   *   Entity type machine name.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Pair of target_id and target_revision_id for given field.
   */
  protected function getReferenceRevisionField(string $field_name, string $entity_type, string $labels): array {
    // Transform titles to ids and maintain the comma separated format.
    $items = explode(',', $labels);
    $items = array_map('trim', $items);
    $ids = [];
    $revision_ids = [];
    foreach ($items as $item) {
      $entity = $this->loadEntityByLabel($entity_type, $item);
      $ids[] = $entity->id();
      $revision_ids[] = $entity->getRevisionId();
    }

    // For revision reference field we have give the target_revision_id.
    return [
      "{$field_name}:target_id" => implode(',', $ids),
      "{$field_name}:target_revision_id" => implode(',', $revision_ids),
    ];
  }

}
