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
  // Invalidate container, so we can discover oe_featured_media field type.
  \Drupal::service('kernel')->invalidateContainer();
}

/**
 * Add new fields to contact entity bundles.
 *
 * The following fields will be created:
 *  - Body text (oe_body)
 *  - Fax number (oe_fax)
 *  - Image (oe_image)
 *  - Mobile number (oe_mobile)
 *  - Office (oe_office)
 *  - Organisation (oe_organisation)
 *  - Press contacts (oe_press_contact_url)
 *  - Website (oe_website)
 */
function oe_content_entity_contact_post_update_00002(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/post_updates/00002_create_contact_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update contact form displays.
 */
function oe_content_entity_contact_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/post_updates/00003_update_contact_form_displays');

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
}

/**
 * Create "oe_details" view mode.
 */
function oe_content_entity_contact_post_update_00004(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/post_updates/00004_create_details_view_mode');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Change "Phone" field cardinality to unlimited.
 */
function oe_content_entity_contact_post_update_00005(): void {
  $oe_phone_field_storage = \Drupal::entityTypeManager()->getStorage('field_storage_config')->load('oe_contact.oe_phone');
  if ($oe_phone_field_storage) {
    $oe_phone_field_storage->set('cardinality', -1);
    $oe_phone_field_storage->save();
  }
}
