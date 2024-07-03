<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Entity Organisation module.
 */

declare(strict_types=1);

use Drupal\Core\Config\FileStorage;

/**
 * Create oe_stakeholder bundle.
 */
function oe_content_entity_organisation_post_update_00001(): void {
  // Obtain configuration from yaml files.
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_entity_organisation') . '/config/post_updates/00001_create_stakeholder_bundle');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Set address subfields as optional.
 */
function oe_content_entity_organisation_post_update_00002(): void {
  $optional_fields = [
    'addressLine1',
    'addressLine2',
    'postalCode',
    'sortingCode',
    'dependentLocality',
    'locality',
    'administrativeArea',
  ];

  $field_config = \Drupal::entityTypeManager()->getStorage('field_config')->load('oe_organisation.oe_stakeholder.oe_address');
  if ($field_config) {
    $field_overrides = $field_config->getSetting('field_overrides');
    foreach ($optional_fields as $optional_field) {
      if (!isset($field_overrides[$optional_field])) {
        // Default configuration is set, so we can update it.
        $field_overrides[$optional_field]['override'] = 'optional';
      }
    }
    $field_config->setSetting('field_overrides', $field_overrides);
    $field_config->save();
  }
}
