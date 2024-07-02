<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

/**
 * Trait for referencing sub-entities inside reference revision fields in Behat.
 */
trait SubEntityReferenceTrait {

  /**
   * Get reference revision field in a multi-value, parsable format.
   *
   * @param string $field_name
   *   Reference revision field name.
   * @param \Drupal\oe_content_sub_entity\Entity\SubEntityInterface[] $entities
   *   The array of sub-entities.
   *
   * @return array
   *   Pair of target_id and target_revision_id for given field.
   */
  protected function getSubEntityReferenceField(string $field_name, array $entities): array {
    $ids = [];
    $revision_ids = [];
    foreach ($entities as $entity) {
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
