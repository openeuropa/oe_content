<?php

/**
 * @file
 * Post update functions for OpenEuropa Organisation Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;

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
 * Enable new dependency.
 */
function oe_content_organisation_post_update_00002(): void {
  \Drupal::service('module_installer')->install(['description_list_field']);
}

/**
 * Create overview field.
 */
function oe_content_organisation_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_organisation') . '/config/post_updates/00003_create_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update entity form display.
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
}
