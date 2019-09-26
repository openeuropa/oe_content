<?php

/**
 * @file
 * Post update functions for OpenEuropa Content module.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Update concept schema for skos concept field types.
 */
function oe_content_post_update_00001_update_concept_schema(): void {
  $config_name_list = \Drupal::entityTypeManager()
    ->getStorage('field_config')
    ->getQuery()
    ->condition('field_type', 'skos_concept_entity_reference')
    ->execute();
  foreach ($config_name_list as $config_name) {
    $field = FieldConfig::load($config_name);
    $settings = $field->getSettings();
    $old_scheme_position = array_search('http://data.europa.eu/uxp', $settings['handler_settings']['concept_schemes']);
    if ($old_scheme_position !== FALSE) {
      $settings['handler_settings']['concept_schemes'][$old_scheme_position] = 'http://data.europa.eu/uxp/det';
      $field->setSettings($settings);
      $field->save();
    }
  }
}
