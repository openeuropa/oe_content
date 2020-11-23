<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\Entity\BaseFieldOverride;

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
 * Install modules and clear cached plugin definitions.
 */
function oe_content_publication_post_update_00003() {
  /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
  $module_handler = \Drupal::service('module_handler');

  /** @var \Drupal\Core\Extension\ModuleInstallerInterface $module_installer */
  $module_installer = \Drupal::service('module_installer');

  $modules = [
    'composite_reference',
    'entity_reference_revisions',
    'field_group',
    'inline_entity_form',
    'link',
    'path',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_documents_field',
    'oe_content_entity_contact',
    'oe_content_organisation_reference',
    'oe_content_reference_code_field',
    'oe_media_avportal',
  ];

  foreach ($modules as $module) {
    if (!$module_handler->moduleExists($module)) {
      $module_installer->install([$module]);
    }
  }

  // Clear the cached plugin definitions of the field types.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();
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

  // Alter the Title field's label.
  $title_config = BaseFieldOverride::load('node.oe_publication.title');
  $title_config->setLabel('Title');
  $title_config->save();

}

/**
 * Update Publication node form displays.
 */
function oe_content_publication_post_update_00006(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_publication') . '/config/post_updates/00006_update_display');

  // Form display configuration to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_publication.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }

  // View display configuration to update.
  $view_display_values = $storage->read('core.entity_view_display.node.oe_publication.default');
  $view_display = EntityViewDisplay::load($view_display_values['id']);
  if ($view_display) {
    $updated_view_display = \Drupal::entityTypeManager()
      ->getStorage($view_display->getEntityTypeId())
      ->updateFromStorageRecord($view_display, $view_display_values);
    $updated_view_display->save();
  }
}
