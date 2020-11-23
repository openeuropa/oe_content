<?php

/**
 * @file
 * OpenEuropa Reference Code Field post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;

/**
 * Create the new multi-value field.
 */
function oe_content_reference_code_field_post_update_00001(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_reference_code_field') . '/config/post_updates/00001_create_field');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}
