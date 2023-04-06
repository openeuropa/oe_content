<?php

/**
 * @file
 * Post update functions for OpenEuropa Organisation Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Set inline entity form widgets reference removal policy to keep entities.
 */
function oe_content_organisation_post_update_00001(): void {
  $form_display = EntityFormDisplay::load('node.oe_organisation.default');
  $component = $form_display->getComponent('oe_organisation_contact');
  $component['settings']['removed_reference'] = 'keep';
  $form_display->setComponent('oe_organisation_contact', $component)->save();
}

/**
 * Enable new dependencies.
 */
function oe_content_organisation_post_update_00002(): void {
  \Drupal::service('module_installer')->install([
    'description_list_field',
    'oe_media',
  ]);
}

/**
 * Create new fields.
 */
function oe_content_organisation_post_update_00003(): void {
  // Create Overview and "Leadership and organisation" related fields.
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_organisation') . '/config/post_updates/00003_create_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update the form display.
 */
function oe_content_organisation_post_update_00004(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_organisation') . '/config/post_updates/00004_update_form_display');

  $form_display_values = $storage->read('core.entity_form_display.node.oe_organisation.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }
}

/**
 * Set contact field cardinality to unlimited.
 */
function oe_content_organisation_post_update_00005(): void {
  $field_storage = FieldStorageConfig::load('node.oe_organisation_contact');
  $field_storage->set('cardinality', '-1');
  $field_storage->save();
  // Update field label.
  $field_config = FieldConfig::load('node.oe_organisation.oe_organisation_contact');
  $field_config->set('label', 'Contacts');
  $field_config->save();
}

/**
 * Creates the subject field.
 */
function oe_content_organisation_post_update_20006() {
  $field = FieldConfig::load('node.oe_organisation.oe_subject');
  if ($field) {
    return 'The subject field already exists for Organisation CT.';
  }
  // Create the subject field.
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_organisation') . '/config/post_updates/20006_subject_field');
  $config_record = $storage->read('field.field.node.oe_organisation.oe_subject');
  $entity_storage = \Drupal::entityTypeManager()->getStorage('field_config');
  $field = $entity_storage->createFromStorageRecord($config_record);
  $field->save();

  // Update the form display.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_organisation.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage('entity_form_display')
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }
}

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_organisation_post_update_20007(): void {
  $fields = [
    'node.oe_organisation.oe_organisation_chart' => FALSE,
    'node.oe_organisation.oe_organisation_contact' => TRUE,
    'node.oe_organisation.oe_organisation_logo' => FALSE,
  ];
  foreach ($fields as $field => $value) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', $value);
    $field_config->save();
  }
}
