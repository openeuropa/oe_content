<?php

/**
 * @file
 * OpenEuropa Content Policy post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Add timeline field to policy content type.
 */
function oe_content_policy_post_update_00001_timeline_field(): void {
  // Clear field type plugin cache.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();

  if (!\Drupal::service('entity_type.manager')->getStorage('field_storage_config')->load('node.oe_timeline')) {
    FieldStorageConfig::create([
      'field_name' => 'oe_timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ])->save();
  }

  if (!\Drupal::service('entity_type.manager')->getStorage('field_config')->load('node.oe_policy.oe_timeline')) {
    FieldConfig::create([
      'label' => 'Timeline',
      'field_name' => 'oe_timeline',
      'entity_type' => 'node',
      'bundle' => 'oe_policy',
      'settings' => [],
      'required' => FALSE,
    ])->save();
  }
}

/**
 * Update body and summary labels.
 */
function oe_content_policy_post_update_00002_field_labels(array &$sandbox): void {
  $new_field_labels = [
    'node.oe_policy.oe_summary' => 'Introduction',
    'node.oe_policy.body' => 'Body text',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }
}
