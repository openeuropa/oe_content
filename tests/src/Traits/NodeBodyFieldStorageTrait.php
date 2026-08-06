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

    // Deleting the fields makes the config dependency system strip the "body"
    // component from every entity form and view display, so preserve them too.
    // A NULL component means that the field was explicitly hidden, which is a
    // different state than not being placed at all.
    $components = [];
    foreach (['entity_form_display', 'entity_view_display'] as $display_entity_type) {
      $displays = $this->container->get('entity_type.manager')
        ->getStorage($display_entity_type)
        ->loadByProperties(['targetEntityType' => 'node']);

      foreach ($displays as $display) {
        $component = $display->getComponent('body');
        if ($component === NULL && !array_key_exists('body', $display->get('hidden') ?? [])) {
          continue;
        }
        $components[$display_entity_type][$display->id()] = $component;
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

    // Put the field back in the displays, with the widget and formatter
    // settings, the weight and the region it had before.
    foreach ($components as $display_entity_type => $display_components) {
      $display_storage = $this->container->get('entity_type.manager')
        ->getStorage($display_entity_type);
      $display_storage->resetCache();

      foreach ($display_components as $id => $component) {
        $display = $display_storage->load($id);
        if ($display === NULL) {
          continue;
        }

        if ($component === NULL) {
          $display->removeComponent('body');
        }
        else {
          $display->setComponent('body', $component);
        }
        $display->save();
      }
    }
  }

}
