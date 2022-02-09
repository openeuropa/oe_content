<?php

/**
 * @file
 * Post update functions for OpenEuropa Event Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\oe_content_event\EventDateRangeFieldTypeChanger;

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
 * Make online link title required.
 */
function oe_content_event_post_update_20002(): void {
  $field_config = FieldConfig::load('node.oe_event.oe_event_online_link');
  $field_config->setSetting('title', 2);
  $field_config->save();
}

/**
 * Enable datetime_range_timezone module.
 */
function oe_content_event_post_update_20003(array &$sandbox) {
  \Drupal::service('module_installer')->install(['datetime_range_timezone']);
  // Clear field type plugin cache.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();
}

/**
 * Convert oe_event_dates field to daterange_timezone.
 */
function oe_content_event_post_update_20004(array &$sandbox) {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_event') . '/config/post_updates/20004_field_dates');
  $field_storage = $storage->read('field.storage.node.oe_event_dates');
  $field_config = $storage->read('field.field.node.oe_event.oe_event_dates');
  EventDateRangeFieldTypeChanger::changeFieldType('oe_event_dates', $field_storage, $field_config);
}

/**
 * Convert oe_event_online_dates field to daterange_timezone.
 */
function oe_content_event_post_update_20005(array &$sandbox) {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_event') . '/config/post_updates/20005_field_online_dates');
  $field_storage = $storage->read('field.storage.node.oe_event_online_dates');
  $field_config = $storage->read('field.field.node.oe_event.oe_event_online_dates');
  EventDateRangeFieldTypeChanger::changeFieldType('oe_event_online_dates', $field_storage, $field_config);
}

/**
 * Convert oe_event_registration_dates field to daterange_timezone.
 */
function oe_content_event_post_update_20006(array &$sandbox) {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_event') . '/config/post_updates/20006_field_registration_dates');
  $field_storage = $storage->read('field.storage.node.oe_event_registration_dates');
  $field_config = $storage->read('field.field.node.oe_event.oe_event_registration_dates');
  EventDateRangeFieldTypeChanger::changeFieldType('oe_event_registration_dates', $field_storage, $field_config);
}

/**
 * Update event form display for date fields use daterange_timezone widget.
 */
function oe_content_event_post_update_20007(array &$sandbox) {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_event') . '/config/post_updates/20007_update_form_display');

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
