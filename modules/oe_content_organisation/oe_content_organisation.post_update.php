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
    'oe_content_person',
  ]);
}

/**
 * Create new fields.
 */
function oe_content_organisation_post_update_00003(): void {
  // Create Overview and "Leadership and organisation" related fields.
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_organisation') . '/config/post_updates/00003_create_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Allow Persons to be referenced and update the form display.
 */
function oe_content_organisation_post_update_00004(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_organisation') . '/config/post_updates/00004_update_form_display');

  $form_display_values = $storage->read('core.entity_form_display.node.oe_organisation.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }

  // Install submodule to allow referencing Person content types.
  \Drupal::service('module_installer')->install([
    'oe_content_organisation_person',
  ]);
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
