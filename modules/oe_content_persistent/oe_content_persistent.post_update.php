<?php

/**
 * @file
 * Post update functions for OpenEuropa Content module.
 */

declare(strict_types = 1);

/**
 * Update PURL configuration.
 */
function oe_content_persistent_post_update_00001(): void {
  $purl_config = \Drupal::configFactory()->getEditable('oe_content_persistent.settings');
  $purl_config->set('supported_entity_types', ['node' => 'node']);
  $purl_config->save();
}
