<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Helper to guarantee a "text_with_summary" node body field storage in tests.
 *
 * Since Drupal 11.4 the node module no longer ships the "body" field storage.
 * The "testing" install profile used by the functional tests ships an optional
 * "field.storage.node.body" of type "text_long", which is installed before -
 * and therefore shadows - the "text_with_summary" storage provided by the
 * node_storage_body_field module that oe_content depends on. As the corporate
 * content types declare their "body" field as "text_with_summary", the type
 * mismatch makes node creation fail with "Property summary is unknown". On
 * Drupal 10 and 11.3 the storage is provided as "text_with_summary" and this
 * helper is a no-op, so it is safe across the whole supported core range.
 *
 * @see https://www.drupal.org/node/3477043
 */
trait NodeBodyFieldStorageTrait {

  /**
   * Recreates the node "body" field storage as "text_with_summary" if needed.
   */
  protected function ensureNodeBodyTextWithSummary(): void {
    $storage = FieldStorageConfig::loadByName('node', 'body');
    if (!$storage instanceof FieldStorageConfig || $storage->getType() === 'text_with_summary') {
      return;
    }

    // Preserve the existing per-bundle field instances so that they can be
    // recreated on top of the corrected storage.
    $field_values = [];
    foreach (FieldConfig::loadMultiple() as $field) {
      if ($field->getTargetEntityTypeId() === 'node' && $field->getName() === 'body') {
        $values = $field->toArray();
        unset($values['uuid'], $values['_core']);
        $field_values[] = $values;
      }
    }

    // Deleting the storage cascades to its field instances.
    $storage->delete();

    // Recreate the storage with the type expected by the corporate content
    // types, mirroring the storage provided by node_storage_body_field.
    FieldStorageConfig::create([
      'field_name' => 'body',
      'entity_type' => 'node',
      'type' => 'text_with_summary',
      'cardinality' => 1,
      'translatable' => TRUE,
    ])->save();

    foreach ($field_values as $values) {
      FieldConfig::create($values)->save();
    }
  }

}
