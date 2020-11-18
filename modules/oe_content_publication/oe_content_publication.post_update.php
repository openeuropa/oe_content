<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;

/**
 * Update body and summary labels.
 */
function oe_content_publication_post_update_00001_update_field_labels(): void {
  $new_field_labels = [
    'node.oe_publication.oe_summary' => 'Introduction',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }
}

/**
 * Enable oe_content_documents_field module.
 */
function oe_content_publication_post_update_00002() {
  \Drupal::service('module_installer')->install(['oe_content_documents_field']);
}

/**
 * Create Contact reference field and update form display.
 */
function oe_content_publication_post_update_00003() {
  $module_installer = \Drupal::service('module_installer');
  if (!\Drupal::service('module_handler')->moduleExists('oe_content_organisation_reference')) {
    $module_installer->install(['oe_content_organisation_reference']);
  }
  $module_installer->install(['path', 'inline_entity_form']);

  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00003_add_contact_reference');

  // Clear the cached plugin definitions of the field types.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();

  // Create the field storage for the reference field.
  $field_storage_config = \Drupal::service('entity_type.manager')->getStorage('field_storage_config');
  if (!$field_storage_config->load('node.oe_publication_contacts')) {
    $reference_field = $storage->read('field.storage.node.oe_publication_contacts');
    // We are creating the config which means that we are also shipping
    // it in the config/install folder so we want to make sure it gets the hash
    // so Drupal treats it as a shipped config. This means that it gets exposed
    // to be translated via the locale system as well.
    $reference_field['_core']['default_config_hash'] = Crypt::hashBase64(serialize($reference_field));
    $field_storage_config->create($reference_field)->save();
  }

  // Create the field config for the reference field.
  $field_config = \Drupal::service('entity_type.manager')->getStorage('field_config');
  if (!$field_config->load('node.oe_publication.oe_publication_contacts')) {
    $reference_field = $storage->read('field.field.node.oe_publication.oe_publication_contacts');
    $reference_field['_core']['default_config_hash'] = Crypt::hashBase64(serialize($reference_field));
    $field_config->create($reference_field)->save();
  }
}

/**
 * Create the new fields in the Publication content type.
 */
function oe_content_publication_post_update_00004(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00004_create_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update field settings.
 */
function oe_content_publication_post_update_00005(): void {
  $new_field_labels = [
    'node.oe_publication.oe_publication_type' => 'Resource type',
    'node.oe_publication.oe_documents' => 'Files',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }

  $new_translatable_settings = [
    'node.oe_publication.oe_summary' => FALSE,
    'node.oe_publication.oe_teaser' => FALSE,
  ];
  foreach ($new_translatable_settings as $id => $value) {
    $field_config = FieldConfig::load($id);
    $field_config->setTranslatable($value);
    $field_config->save();
  }
}

/**
 * Update Publication node form display.
 */
function oe_content_publication_post_update_00006(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00006_update_form_display');

  // Form display configurations to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_publication.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }
}
