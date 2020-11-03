<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Update body and summary labels.
 */
function oe_content_publication_post_update_00001_update_field_labels(array &$sandbox): void {
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
  if (!\Drupal::service('module_handler')->moduleExists('oe_content_organisation_reference')) {
    \Drupal::service('module_installer')->install(['oe_content_organisation_reference']);
  }

  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00003_contact_reference');

  // Clear the cached plugin definitions of the field types.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();

  $field_storage_config = \Drupal::service('entity_type.manager')->getStorage('field_storage_config');
  if (!$field_storage_config->load('node.oe_publication_contact')) {
    $reference_field = $storage->read('field.storage.node.oe_publication_contact');
    $field_storage_config->create($reference_field)->save();
  }

  $field_config = \Drupal::service('entity_type.manager')->getStorage('field_config');
  if (!$field_config->load('node.oe_publication.oe_publication_contact')) {
    $reference_field = $storage->read('field.field.node.oe_publication.oe_publication_contact');
    $field_config->create($reference_field)->save();
  }

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
