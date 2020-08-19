<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Entity Organisation module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;

/**
 * Update configuration for oe_stakeholder.
 */
function oe_content_entity_organisation_post_update_00001(): void {
  // Obtain configuration from yaml files.
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_organisation') . '/config/post_updates/00001_oe_stakeholder');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}
