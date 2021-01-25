<?php

/**
 * @file
 * OpenEuropa Content Timeline Field post updates.
 */

declare(strict_types = 1);

/**
 * Update table field charset to default.
 */
function oe_content_timeline_field_post_update_00004(&$sandbox) {
  /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
  $entity_field_manager = \Drupal::service('entity_field.manager');
  $timelines = $entity_field_manager->getFieldMapByFieldType('timeline_field');
  $key_value = \Drupal::keyValue('entity.storage_schema.sql');
  $db = \Drupal::database();
  foreach ($timelines as $entity_type => $fields) {
    foreach (array_keys($fields) as $field_name) {
      $key_name = $entity_type . '.field_schema_data.' . $field_name;
      $storage_schema = $key_value->get($key_name);
      // Update all tables where the field is present.
      foreach ($storage_schema as &$table_schema) {
        foreach (['label', 'format'] as $column) {
          $table_schema['fields'][$field_name . '_' . $column]['type'] = 'varchar';
        }
      }
      $key_value->set($key_name, $storage_schema);
      foreach ($storage_schema as $table_name => $table_schema) {
        foreach (['label', 'format'] as $column) {
          $db->schema()->changeField($table_name, $field_name . '_' . $column, $field_name . '_' . $column, $table_schema['fields'][$field_name . '_' . $column]);
        }
      }
    }
  }
}
