<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Persistent module.
 */

declare(strict_types=1);

/**
 * Update PURL configuration.
 */
function oe_content_persistent_post_update_00001(): void {
  if (\Drupal::getContainer()->hasParameter('oe_content_persistent.supported_entity_types')) {
    $supported_entity_types = \Drupal::getContainer()->getParameter('oe_content_persistent.supported_entity_types');
  }
  if (empty($supported_entity_types)) {
    $supported_entity_types = ['node'];
  }
  $supported_entity_types = array_combine($supported_entity_types, $supported_entity_types);
  $purl_config = \Drupal::configFactory()->getEditable('oe_content_persistent.settings');
  $purl_config->set('supported_entity_types', $supported_entity_types);
  $purl_config->save();
}
