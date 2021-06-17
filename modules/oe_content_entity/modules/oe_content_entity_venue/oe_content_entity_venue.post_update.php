<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Entity Organisation module.
 */

declare(strict_types = 1);

/**
 * Set address subfields as optional.
 */
function oe_content_entity_venue_post_update_00001(): void {
  $optional_fields = [
    'addressLine1',
    'addressLine2',
    'postalCode',
    'sortingCode',
    'locality',
    'administrativeArea',
  ];

  $field_config = \Drupal::entityTypeManager()->getStorage('field_config')->load('oe_venue.oe_default.oe_address');
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
