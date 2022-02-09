<?php

/**
 * @file
 * Post update functions for OpenEuropa Event Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Make the Event venue and contact fields composite.
 */
function oe_content_event_post_update_00001(array &$sandbox) {
  \Drupal::service('module_installer')->install(['composite_reference']);

  $fields = [
    'node.oe_event.oe_event_venue',
    'node.oe_event.oe_event_contact',
  ];
  foreach ($fields as $field) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite', TRUE);
    $field_config->save();
  }
}

/**
 * Make the Event venue not required.
 */
function oe_content_event_post_update_00002(array &$sandbox): void {
  $field_config = FieldConfig::load('node.oe_event.oe_event_venue');
  $field_config->set('required', FALSE);
  $field_config->save();
}

/**
 * Change event venue reference widget to inline_entity_form_complex.
 */
function oe_content_event_post_update_00003(array &$sandbox): void {
  $form_display = EntityFormDisplay::load('node.oe_event.default');
  $content = $form_display->get('content') ?: [];
  if (!isset($content['oe_event_venue'])) {
    return;
  }

  $content['oe_event_venue']['type'] = 'inline_entity_form_complex';
  $content['oe_event_venue']['settings'] = [
    'form_mode' => 'default',
    'revision' => TRUE,
    'override_labels' => TRUE,
    'label_singular' => 'venue',
    'label_plural' => 'venues',
    'collapsible' => TRUE,
    'allow_new' => TRUE,
    'match_operator' => 'CONTAINS',
    'collapsed' => FALSE,
    'allow_existing' => FALSE,
    'allow_duplicate' => FALSE,
  ];
  $content['oe_event_venue']['third_party_settings'] = [];
  $form_display->set('content', $content);
  $form_display->save();
}

/**
 * Fix auto_create_bundle on event contact reference field.
 */
function oe_content_event_post_update_00004(array &$sandbox): void {
  $field_config = FieldConfig::load('node.oe_event.oe_event_contact');
  $settings = $field_config->get('settings');
  if ($settings['handler'] !== 'default:oe_contact') {
    return;
  }
  $settings['handler_settings']['auto_create_bundle'] = 'oe_general';
  $field_config->set('settings', $settings);
  $field_config->save();
}

/**
 * Set inline entity form widget reference removal policy to keep entities.
 */
function oe_content_event_post_update_00005(): void {
  $form_display = EntityFormDisplay::load('node.oe_event.default');

  foreach (['oe_event_contact', 'oe_event_venue'] as $field_name) {
    $component = $form_display->getComponent($field_name);
    $component['settings']['removed_reference'] = 'keep';
    $form_display->setComponent($field_name, $component);
  }

  $form_display->save();
}

/**
 * Add Media and Programme fields.
 */
function oe_content_event_post_update_20001(): void {
  \Drupal::service('module_installer')->install(['oe_content_event_event_programme']);
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_event') . '/config/post_updates/20001_event_v2_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update form display.
 */
function oe_content_event_post_update_20002(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_event') . '/config/post_updates/20002_update_form_display');

  // Form display configurations to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_event.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }
}

/**
 * Make online link title required.
 */
function oe_content_event_post_update_20003(): void {
  $field_config = FieldConfig::load('node.oe_event.oe_event_online_link');
  $field_config->setSetting('title', 2);
  $field_config->save();
}

/**
 * Enable datetime_range_timezone module.
 */
function oe_content_event_post_update_20004(array &$sandbox) {
  \Drupal::service('module_installer')->install(['datetime_range_timezone']);
}

/**
 * Convert oe_event_dates field to daterange_timezone.
 */
function oe_content_event_post_update_20005(array &$sandbox) {
  $database = \Drupal::database();
  $field = 'oe_event_dates';

  // Check if we have any records in the field data table.
  $count = $database->select("node__$field", 'n')
    ->countQuery()
    ->execute()
    ->fetchField();
  if ($count > 0) {
    // Create a table based on the one we want to back up.
    $data_table = "node__$field";
    $backup_data_table = "_node__$field";
    $query_string = 'CREATE TABLE ' . $backup_data_table . ' LIKE ' . $data_table;
    $database->query($query_string);

    // Copy every record from the field table to the backup table.
    $query_string = 'INSERT ' . $backup_data_table . ' SELECT * FROM ' . $data_table;
    $database->query($query_string);

    // Now do the same for the field revisions table.
    $revision_table = "node_revision__$field";
    $backup_revision_table = "_node_revision__$field";
    $query_string = 'CREATE TABLE ' . $backup_revision_table . ' LIKE ' . $revision_table;
    $database->query($query_string);
    $query_string = 'INSERT ' . $backup_revision_table . ' SELECT * FROM ' . $revision_table;
    $database->query($query_string);
  }

  // Load up the form display in its original state.
  $form_display = \Drupal::service('entity_display.repository')
    ->getFormDisplay('node', 'oe_event', 'default');

  /** @var \Drupal\field\Entity\FieldStorageConfig $field_config */
  $field_storage = FieldStorageConfig::load("node.$field");
  $new_field_storage = $field_storage;
  $new_field_storage->set('type', 'daterange_timezone');
  $new_field_storage = $new_field_storage->toArray();
  $new_field_storage = FieldStorageConfig::create($new_field_storage);

  /** @var \Drupal\field\Entity\FieldConfig $field_config */
  $field_config = FieldConfig::load("node.oe_event.$field");
  $new_field_config = $field_config;
  $new_field_config->set('field_type', 'daterange_timezone');
  $new_field_config = $new_field_config->toArray();
  $new_field_config = FieldConfig::create($new_field_config);
  $field_config->delete();

  // Purge field data.
  field_purge_batch(50);

  // Save the new field.
  $new_field_storage->save();
  $new_field_config->save();

  // Update form display.
  $component = $form_display->getComponent($field);
  $component['type'] = 'daterange_timezone';
  $form_display->setComponent($field, $component);
  $form_display->save();

  if ($count > 0) {
    // Now we need to account for an additional column for timezone, so we
    // change the schema of the backup table accordingly.
    $query_string = 'ALTER TABLE ' . $backup_data_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
    $database->query($query_string);
    $query_string = 'ALTER TABLE ' . $backup_revision_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
    $database->query($query_string);

    // Now make sure we add 'Europe/Brussels' timezone.
    $query_string = 'UPDATE ' . $backup_data_table . ' SET ' . $field . '_timezone = \'Europe/Brussels\'';
    $database->query($query_string);
    $query_string = 'UPDATE ' . $backup_revision_table . ' SET ' . $field . '_timezone = \'Europe/Brussels\'';
    $database->query($query_string);

    // Restore existing data in the same field table.
    $query_string = 'INSERT ' . $data_table . ' SELECT * FROM ' . $backup_data_table;
    $database->query($query_string);
    $query_string = 'INSERT ' . $revision_table . ' SELECT * FROM ' . $backup_revision_table;
    $database->query($query_string);

    // Delete the backup tables from the database.
    $database->query('DROP TABLE ' . $backup_data_table);
    $database->query('DROP TABLE ' . $backup_revision_table);
  }
}

/**
 * Convert oe_event_online_dates field to daterange_timezone.
 */
function oe_content_event_post_update_20006(array &$sandbox) {
  $database = \Drupal::database();
  $field = 'oe_event_online_dates';

  // Check if we have any records in the field data table.
  $count = $database->select("node__$field", 'n')
    ->countQuery()
    ->execute()
    ->fetchField();
  if ($count > 0) {
    // Create a table based on the one we want to back up.
    $data_table = "node__$field";
    $backup_data_table = "_node__$field";
    $query_string = 'CREATE TABLE ' . $backup_data_table . ' LIKE ' . $data_table;
    $database->query($query_string);

    // Copy every record from the field table to the backup table.
    $query_string = 'INSERT ' . $backup_data_table . ' SELECT * FROM ' . $data_table;
    $database->query($query_string);

    // Now do the same for the field revisions table.
    $revision_table = "node_revision__$field";
    $backup_revision_table = "_node_revision__$field";
    $query_string = 'CREATE TABLE ' . $backup_revision_table . ' LIKE ' . $revision_table;
    $database->query($query_string);
    $query_string = 'INSERT ' . $backup_revision_table . ' SELECT * FROM ' . $revision_table;
    $database->query($query_string);
  }

  // Load up the form display in its original state.
  $form_display = \Drupal::service('entity_display.repository')
    ->getFormDisplay('node', 'oe_event', 'default');

  /** @var \Drupal\field\Entity\FieldStorageConfig $field_config */
  $field_storage = FieldStorageConfig::load("node.$field");
  $new_field_storage = $field_storage;
  $new_field_storage->set('type', 'daterange_timezone');
  $new_field_storage = $new_field_storage->toArray();
  $new_field_storage = FieldStorageConfig::create($new_field_storage);

  /** @var \Drupal\field\Entity\FieldConfig $field_config */
  $field_config = FieldConfig::load("node.oe_event.$field");
  $new_field_config = $field_config;
  $new_field_config->set('field_type', 'daterange_timezone');
  $new_field_config = $new_field_config->toArray();
  $new_field_config = FieldConfig::create($new_field_config);
  $field_config->delete();

  // Purge field data.
  field_purge_batch(50);

  // Save the new field.
  $new_field_storage->save();
  $new_field_config->save();

  // Update form display.
  $component = $form_display->getComponent($field);
  $component['type'] = 'daterange_timezone';
  $form_display->setComponent($field, $component);
  $form_display->save();

  if ($count > 0) {
    // Now we need to account for an additional column for timezone, so we
    // change the schema of the backup table accordingly.
    $query_string = 'ALTER TABLE ' . $backup_data_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
    $database->query($query_string);
    $query_string = 'ALTER TABLE ' . $backup_revision_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
    $database->query($query_string);

    // Now make sure we add 'Europe/Brussels' timezone.
    $query_string = 'UPDATE ' . $backup_data_table . ' SET ' . $field . '_timezone = \'Europe/Brussels\'';
    $database->query($query_string);
    $query_string = 'UPDATE ' . $backup_revision_table . ' SET ' . $field . '_timezone = \'Europe/Brussels\'';
    $database->query($query_string);

    // Restore existing data in the same field table.
    $query_string = 'INSERT ' . $data_table . ' SELECT * FROM ' . $backup_data_table;
    $database->query($query_string);
    $query_string = 'INSERT ' . $revision_table . ' SELECT * FROM ' . $backup_revision_table;
    $database->query($query_string);

    // Delete the backup tables from the database.
    $database->query('DROP TABLE ' . $backup_data_table);
    $database->query('DROP TABLE ' . $backup_revision_table);
  }
}

/**
 * Convert oe_event_registration_dates field to daterange_timezone.
 */
function oe_content_event_post_update_20007(array &$sandbox) {
  $database = \Drupal::database();
  $field = 'oe_event_registration_dates';

  // Check if we have any records in the field data table.
  $count = $database->select("node__$field", 'n')
    ->countQuery()
    ->execute()
    ->fetchField();
  if ($count > 0) {
    // Create a table based on the one we want to back up.
    $data_table = "node__$field";
    $backup_data_table = "_node__$field";
    $query_string = 'CREATE TABLE ' . $backup_data_table . ' LIKE ' . $data_table;
    $database->query($query_string);

    // Copy every record from the field table to the backup table.
    $query_string = 'INSERT ' . $backup_data_table . ' SELECT * FROM ' . $data_table;
    $database->query($query_string);

    // Now do the same for the field revisions table.
    $revision_table = "node_revision__$field";
    $backup_revision_table = "_node_revision__$field";
    $query_string = 'CREATE TABLE ' . $backup_revision_table . ' LIKE ' . $revision_table;
    $database->query($query_string);
    $query_string = 'INSERT ' . $backup_revision_table . ' SELECT * FROM ' . $revision_table;
    $database->query($query_string);
  }

  // Load up the form display in its original state.
  $form_display = \Drupal::service('entity_display.repository')
    ->getFormDisplay('node', 'oe_event', 'default');

  /** @var \Drupal\field\Entity\FieldStorageConfig $field_config */
  $field_storage = FieldStorageConfig::load("node.$field");
  $new_field_storage = $field_storage;
  $new_field_storage->set('type', 'daterange_timezone');
  $new_field_storage = $new_field_storage->toArray();
  $new_field_storage = FieldStorageConfig::create($new_field_storage);

  /** @var \Drupal\field\Entity\FieldConfig $field_config */
  $field_config = FieldConfig::load("node.oe_event.$field");
  $new_field_config = $field_config;
  $new_field_config->set('field_type', 'daterange_timezone');
  $new_field_config = $new_field_config->toArray();
  $new_field_config = FieldConfig::create($new_field_config);
  $field_config->delete();

  // Purge field data.
  field_purge_batch(50);

  // Save the new field.
  $new_field_storage->save();
  $new_field_config->save();

  // Update form display.
  $component = $form_display->getComponent($field);
  $component['type'] = 'daterange_timezone';
  $form_display->setComponent($field, $component);
  $form_display->save();

  if ($count > 0) {
    // Now we need to account for an additional column for timezone, so we
    // change the schema of the backup table accordingly.
    $query_string = 'ALTER TABLE ' . $backup_data_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
    $database->query($query_string);
    $query_string = 'ALTER TABLE ' . $backup_revision_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
    $database->query($query_string);

    // Now make sure we add 'Europe/Brussels' timezone.
    $query_string = 'UPDATE ' . $backup_data_table . ' SET ' . $field . '_timezone = \'Europe/Brussels\'';
    $database->query($query_string);
    $query_string = 'UPDATE ' . $backup_revision_table . ' SET ' . $field . '_timezone = \'Europe/Brussels\'';
    $database->query($query_string);

    // Restore existing data in the same field table.
    $query_string = 'INSERT ' . $data_table . ' SELECT * FROM ' . $backup_data_table;
    $database->query($query_string);
    $query_string = 'INSERT ' . $revision_table . ' SELECT * FROM ' . $backup_revision_table;
    $database->query($query_string);

    // Delete the backup tables from the database.
    $database->query('DROP TABLE ' . $backup_data_table);
    $database->query('DROP TABLE ' . $backup_revision_table);
  }
}
