<?php

/**
 * @file
 * OpenEuropa Contact entity post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;

/**
 * Update Contact entity.
 *
 * Add new fields to the Contact entity:
 *  - Body text (oe_body);
 *  - Fax number (oe_fax);
 *  - Image (oe_image).
 *  - Mobile number (oe_mobile);
 *  - Office (oe_office);
 *  - Organisation (oe_organisation);
 *  - Press contacts (oe_press_contact_url);
 *  - Website (oe_website).
 * Change cardinality of the Phone field.
 */
function oe_content_entity_contact_post_update_00001(): void {
  // Enable module that provides "Featured media" field type.
  \Drupal::service('module_installer')->install(['oe_content_featured_media_field']);

  // Create a file storage instance for reading configurations.
  $file_storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/install');

  // Create new fields.
  $config_manager_helper = \Drupal::service('oe_content.config_manager_helper');
  $fields = [
    'oe_body',
    'oe_fax',
    'oe_image',
    'oe_mobile',
    'oe_office',
    'oe_organisation',
    'oe_press_contact_url',
    'oe_website',
  ];
  foreach ($fields as $field_name) {
    $config_manager_helper->createConfig("field.storage.oe_contact.$field_name", $file_storage);
    $config_manager_helper->createConfig("field.field.oe_contact.oe_general.$field_name", $file_storage);
    $config_manager_helper->createConfig("field.field.oe_contact.oe_press.$field_name", $file_storage);
  }

  // Update form view.
  $config_manager_helper->updateConfig('core.entity_form_display.oe_contact.oe_general.default', $file_storage);
  $config_manager_helper->updateConfig('core.entity_form_display.oe_contact.oe_press.default', $file_storage);

  // Create "Details" view mode.
  $config_manager_helper->createConfig('core.entity_view_mode.oe_contact.oe_details', $file_storage);
  $config_manager_helper->createConfig('core.entity_view_display.oe_contact.oe_general.oe_details', $file_storage);
  $config_manager_helper->createConfig('core.entity_view_display.oe_contact.oe_press.oe_details', $file_storage);

  // Change cardinality of the Phone field.
  $config_manager_helper->updateConfig('field.storage.oe_contact.oe_phone', $file_storage, ['cardinality']);

  // Clear caches.
  \Drupal::service('kernel')->invalidateContainer();
  \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
}
