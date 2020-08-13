<?php

/**
 * @file
 * OpenEuropa Contact entity post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Config\FileStorage;

/**
 * Enable dependencies: oe_content_featured_media_field, oe_media.
 */
function oe_content_entity_contact_post_update_00001(): void {
  \Drupal::service('module_installer')->install([
    'oe_content_featured_media_field',
    'oe_media',
  ]);
}

/**
 * Create new configuration for Contact entity.
 *
 * Add new fields:
 *  - Body text (oe_body);
 *  - Fax number (oe_fax);
 *  - Image (oe_image).
 *  - Mobile number (oe_mobile);
 *  - Office (oe_office);
 *  - Organisation (oe_organisation);
 *  - Press contacts (oe_press_contact_url);
 *  - Website (oe_website).
 *
 * Create view mode "oe_details".
 */
function oe_content_entity_contact_post_update_00002(): void {
  // Create a file storage instance for reading configurations.
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/post_updates/00002_create_oe_contact_fields');

  // Create new configurations.
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Change cardinality of the Phone field.
 */
function oe_content_entity_contact_post_update_00003(): void {
  // Create a file storage instance for reading configurations.
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/post_updates/00003_update_oe_contact_form_displays');

  // Form display configurations to update.
  $form_displays = [
    'core.entity_form_display.oe_contact.oe_general.default',
    'core.entity_form_display.oe_contact.oe_press.default',
  ];
  foreach ($form_displays as $display) {
    $values = $storage->read($display);
    $config = EntityFormDisplay::load($values['id']);
    if ($config) {
      foreach ($values as $key => $value) {
        $config->set($key, $value);
      }
      $config->save();
    }
  }

  // Update cardinality of oe_phone field.
  $oe_phone_field_storage = \Drupal::entityTypeManager()->getStorage('field_storage_config')->load('oe_contact.oe_phone');
  if ($oe_phone_field_storage) {
    $oe_phone_field_storage->set('cardinality', -1);
    $oe_phone_field_storage->save();
  }
}
