<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
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
 * Create the new fields in the Publication content type.
 */
function oe_content_publication_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00003_create_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update field labels.
 */
function oe_content_publication_post_update_00004(): void {
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
    'node.oe_publication.oe_publication_type' => TRUE,
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
function oe_content_publication_post_update_00005(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00005_update_form_display');

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

/**
 * Update Publication node view display.
 */
function oe_content_publication_post_update_00006(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00006_update_view_display');

  // View display configurations to update.
  $view_display_values = $storage->read('core.entity_view_display.node.oe_publication.default');
  $view_display = EntityViewDisplay::load($view_display_values['id']);
  if ($view_display) {
    $updated_view_display = \Drupal::entityTypeManager()
      ->getStorage($view_display->getEntityTypeId())
      ->updateFromStorageRecord($view_display, $view_display_values);
    $updated_view_display->save();
  }
}
