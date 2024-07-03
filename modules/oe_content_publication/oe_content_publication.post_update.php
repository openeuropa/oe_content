<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types=1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Field\Entity\BaseFieldOverride;
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
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_publication') . '/config/post_updates/00004_create_fields');

  $configs = [
    'field_storage_config' => [
      'field.storage.node.oe_publication_contacts' => 'node.oe_publication_contacts',
      'field.storage.node.oe_publication_countries' => 'node.oe_publication_countries',
      'field.storage.node.oe_publication_last_updated' => 'node.oe_publication_last_updated',
      'field.storage.node.oe_publication_thumbnail' => 'node.oe_publication_thumbnail',
      'field.storage.node.oe_reference_codes' => 'node.oe_reference_codes',
    ],

    'field_config' => [
      'field.field.node.oe_publication.body' => 'node.oe_publication.body',
      'field.field.node.oe_publication.oe_departments' => 'node.oe_publication.oe_departments',
      'field.field.node.oe_publication.oe_publication_contacts' => 'node.oe_publication.oe_publication_contacts',
      'field.field.node.oe_publication.oe_publication_countries' => 'node.oe_publication.oe_publication_countries',
      'field.field.node.oe_publication.oe_publication_last_updated' => 'node.oe_publication.oe_publication_last_updated',
      'field.field.node.oe_publication.oe_publication_thumbnail' => 'node.oe_publication.oe_publication_thumbnail',
      'field.field.node.oe_publication.oe_reference_codes' => 'node.oe_publication.oe_reference_codes',
    ],
  ];

  foreach ($configs as $key => $ids) {
    $config_storage = \Drupal::entityTypeManager()->getStorage($key);
    foreach ($ids as $id => $reference) {
      if (!$config_storage->load($reference)) {
        $config_data = $storage->read($id);
        // We are creating the config which means that we are also shipping
        // it in the install folder so we want to make sure it gets the hash
        // so Drupal treats it as a shipped config. This means that it gets
        // exposed to be translated via the locale system as well.
        $config_data['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config_data));
        $config_storage->create($config_data)->save();
      }
    }
  }
}

/**
 * Update field settings.
 */
function oe_content_publication_post_update_00005(): void {
  // Change field labels.
  $new_field_labels = [
    'node.oe_publication.oe_publication_type' => 'Resource type',
    'node.oe_publication.oe_documents' => 'Files',
    'node.oe_publication.oe_author' => 'Author',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }

  // Alter the Title field's label.
  $title_config = BaseFieldOverride::load('node.oe_publication.title');
  $title_config->setLabel('Title');
  $title_config->save();

  // Set default publication type value.
  $field_config = FieldConfig::load('node.oe_publication.oe_publication_type');
  $field_config->set('default_value', [
    ['target_id' => 'http://publications.europa.eu/resource/authority/resource-type/PUB_GEN'],
  ]);
  $field_config->save();
}

/**
 * Update Publication node form displays.
 */
function oe_content_publication_post_update_00006(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_publication') . '/config/post_updates/00006_update_display');

  // Form display configuration to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_publication.default');
  $form_display_values['_core']['default_config_hash'] = Crypt::hashBase64(serialize($form_display_values));
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }

  // View display configuration to update.
  $view_display_values = $storage->read('core.entity_view_display.node.oe_publication.default');
  $view_display_values['_core']['default_config_hash'] = Crypt::hashBase64(serialize($view_display_values));
  $view_display = EntityViewDisplay::load($view_display_values['id']);
  if ($view_display) {
    $updated_view_display = \Drupal::entityTypeManager()
      ->getStorage($view_display->getEntityTypeId())
      ->updateFromStorageRecord($view_display, $view_display_values);
    $updated_view_display->save();
  }
}

/**
 * Set inline entity form widgets reference removal policy to keep entities.
 */
function oe_content_publication_post_update_00007(): void {
  $form_display = EntityFormDisplay::load('node.oe_publication.default');
  $component = $form_display->getComponent('oe_publication_contacts');
  $component['settings']['removed_reference'] = 'keep';
  $form_display->setComponent('oe_publication_contacts', $component)->save();
}

/**
 * Publication v3: Add publication collection related fields.
 */
function oe_content_publication_post_update_20001(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_publication') . '/config/post_updates/20001_publication_collection');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Publication v3: Update form display.
 */
function oe_content_publication_post_update_20002(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_publication') . '/config/post_updates/20002_publication_collection');

  // Form display configuration to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_publication.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }

  // Set the oe_documents field not required.
  $field_config = FieldConfig::load('node.oe_publication.oe_documents');
  $field_config->setRequired(FALSE);
  $field_config->save();
}

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_publication_post_update_20003(): void {
  $fields = [
    'node.oe_publication.oe_publication_contacts' => TRUE,
    'node.oe_publication.oe_publication_publications' => FALSE,
    'node.oe_publication.oe_publication_thumbnail' => FALSE,
  ];
  foreach ($fields as $field => $value) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', $value);
    $field_config->save();
  }
}
