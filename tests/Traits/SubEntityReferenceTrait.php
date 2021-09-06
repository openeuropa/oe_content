<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\Tests\oe_content\Behat\Content\Traits\GatherSubEntityContextTrait;

/**
 * Trait for referencing sub-entities inside reference revision fields in Behat.
 */
trait SubEntityReferenceTrait {

  use GatherSubEntityContextTrait;

  /**
   * Get reference revision field in a multi-value, parsable format.
   *
   * @param string $field_name
   *   Reference revision field name.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Pair of target_id and target_revision_id for given field.
   */
  protected function getSubEntityReferenceField(string $field_name, string $labels): array {
    $ids = [];
    $revision_ids = [];
    $labels = explode(', ', $labels);
    foreach ($labels as $name) {
      $entity = $this->subEntityContext->getSubEntityByName($name);
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
