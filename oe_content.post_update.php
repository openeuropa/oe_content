<?php

/**
 * @file
 * Post update functions for OpenEuropa Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Utility\UpdateException;
use Drupal\field\Entity\FieldConfig;

/**
 * Update concept schema for skos concept field types.
 */
function oe_content_post_update_00001(): void {
  $storage = \Drupal::entityTypeManager()->getStorage('skos_concept_scheme');
  $det = $storage->load('http://data.europa.eu/uxp/det');
  if (empty($det)) {
    $message = 'Digital Europa Thesaurus concept scheme not found. '
      . 'Please make sure to run a triple store that contains the official and up-to-date DET vocabulary from the EC Publications Office. '
      . 'If you are using the triple-store-dev image, update to version 1.1.0 or later.';
    throw new UpdateException($message);
  }

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

      // The default SKOS entity reference selection plugin stores the concept
      // schemes also in the field array.
      if (isset($settings['handler_settings']['field']['concept_schemes'])) {
        $old_scheme_position = array_search('http://data.europa.eu/uxp', $settings['handler_settings']['field']['concept_schemes']);
        if ($old_scheme_position !== FALSE) {
          $settings['handler_settings']['field']['concept_schemes'][$old_scheme_position] = 'http://data.europa.eu/uxp/det';
        }
      }

      $field->setSettings($settings);
      $field->save();
    }
  }
}

/**
 * Add Country and Language corporate vocabulary.
 */
function oe_content_post_update_00002(): void {
  $config = \Drupal::configFactory()->getEditable('rdf_skos.graphs');
  $entity_types = $config->get('entity_types');
  $vocabularies = [
    [
      'name' => 'country',
      'uri' => 'http://publications.europa.eu/resource/authority/country',
    ],
    [
      'name' => 'language',
      'uri' => 'http://publications.europa.eu/resource/authority/language',
    ],
  ];
  $original_config_size = count($entity_types['skos_concept']);

  foreach ($vocabularies as $vocabulary) {
    // Check if vocabulary already exists.
    foreach ($entity_types['skos_concept_scheme'] as $scheme) {
      if ($scheme['name'] === $vocabulary['name']) {
        continue 2;
      }
    }
    $entity_types['skos_concept_scheme'][] = [
      'name' => $vocabulary['name'],
      'uri' => $vocabulary['uri'],
    ];
    $entity_types['skos_concept'][] = [
      'name' => $vocabulary['name'],
      'uri' => $vocabulary['uri'],
    ];
  }

  // Avoid saving if the config is not changed.
  if (count($entity_types['skos_concept']) !== $original_config_size) {
    $config->set('entity_types', $entity_types)->save();
  }
}
