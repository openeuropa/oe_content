<?php

/**
 * @file
 * OpenEuropa Content Policy post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Add missing permission for Validator.
 */
function oe_content_policy_post_update_00001_timeline_field(array &$sandbox): void {
  FieldStorageConfig::create([
    'field_name' => 'oe_timeline',
    'entity_type' => 'node',
    'type' => 'timeline_field',
    'cardinality' => -1,
    'entity_types' => ['node'],
  ])->save();

  FieldConfig::create([
    'label' => 'Timeline',
    'field_name' => 'oe_timeline',
    'entity_type' => 'node',
    'bundle' => 'oe_policy',
    'settings' => [],
    'required' => FALSE,
  ])->save();
}
