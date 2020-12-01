<?php

/**
 * @file
 * OpenEuropa Contact entity post updates.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityViewMode;

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
  foreach ($form_displays as $form_display) {
    $form_display_values = $storage->read($form_display);
    $form_display = EntityFormDisplay::load($form_display_values['id']);
    if ($form_display) {
      $updated_form_display = \Drupal::entityTypeManager()
        ->getStorage($form_display->getEntityTypeId())
        ->updateFromStorageRecord($form_display, $form_display_values);
      $updated_form_display->save();
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

/**
 * Hide "Organisation" property from Address field.
 */
function oe_content_entity_contact_post_update_00006(): void {
  $bundles = ['oe_general', 'oe_press'];
  foreach ($bundles as $bundle) {
    $field_config = \Drupal::entityTypeManager()->getStorage('field_config')->load("oe_contact.$bundle.oe_address");
    if ($field_config) {
      // Field exists.
      $field_overrides = $field_config->getSetting('field_overrides');
      if (!isset($field_overrides['organization'])) {
        // Default configuration is set, so we can update it.
        $field_overrides['organization']['override'] = 'hidden';
        $field_config->setSetting('field_overrides', $field_overrides);
        $field_config->save();
      }
    }
  }
}

/**
 * Create the Contact view mode and reference field storage.
 */
function oe_content_entity_contact_post_update_00007(): void {

  $view_mode_config = [
    'id' => 'node.oe_contact',
    'label' => 'Contact',
    'targetEntityType' => 'node',
  ];
  // We are creating the config which means that we are also shipping
  // it in the config/install folder so we want to make sure it gets the hash
  // so Drupal treats it as a shipped config. This means that it gets exposed
  // to be translated via the locale system as well.
  $view_mode_config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($view_mode_config));
  EntityViewMode::create($view_mode_config)->save();

  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_contact') . '/config/post_updates/00007_node_reference_field');

  // Clear the cached plugin definitions of the field types.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();

  // Create the field storage for the reference field.
  $field_storage_config = \Drupal::service('entity_type.manager')->getStorage('field_storage_config');
  if (!$field_storage_config->load('oe_contact.oe_node_reference')) {
    $reference_field = $storage->read('field.storage.oe_contact.oe_node_reference');
    $field_storage_config->create($reference_field)->save();
  }
}
