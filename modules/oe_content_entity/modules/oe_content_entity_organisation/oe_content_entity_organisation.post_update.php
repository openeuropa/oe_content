<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Entity Organisation module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;

/**
 * Create oe_stakeholder bundle.
 */
function oe_content_entity_organisation_post_update_00001(): void {
  // Obtain configuration from yaml files.
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_entity_organisation') . '/config/post_updates/00001_create_stakeholder_bundle');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Enable oe_content_organisation_reference module.
 */
function oe_content_entity_organisation_post_update_00002() {
  \Drupal::service('module_installer')->install(['oe_content_organisation_reference']);
}
