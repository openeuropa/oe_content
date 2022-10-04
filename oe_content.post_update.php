<?php

/**
 * @file
 * Post update functions for OpenEuropa Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Utility\UpdateException;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\Role;

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
 * Add Country corporate vocabulary.
 */
function oe_content_post_update_00002(): void {
  $config = \Drupal::configFactory()->getEditable('rdf_skos.graphs');

  $entity_types = $config->get('entity_types');

  $name = 'country';
  $graph = 'http://publications.europa.eu/resource/authority/country';
  $entity_types['skos_concept_scheme'][] = [
    'name' => $name,
    'uri' => $graph,
  ];
  $entity_types['skos_concept'][] = [
    'name' => $name,
    'uri' => $graph,
  ];

  $config->set('entity_types', $entity_types)->save();
}

/**
 * Add Language corporate vocabulary.
 */
function oe_content_post_update_00003(): void {
  $config = \Drupal::configFactory()->getEditable('rdf_skos.graphs');
  $entity_types = $config->get('entity_types');
  $name = 'language';
  $graph = 'http://publications.europa.eu/resource/authority/language';

  $entity_types['skos_concept_scheme'][] = [
    'name' => $name,
    'uri' => $graph,
  ];
  $entity_types['skos_concept'][] = [
    'name' => $name,
    'uri' => $graph,
  ];

  $config->set('entity_types', $entity_types)->save();
}

/**
 * Fix invalid corporate entity CRUD permissions.
 */
function oe_content_post_update_00004(): void {
  $permissions = [
    // Contact invalid and valid permissions.
    'create oe_press corporate entity' => 'create oe_contact oe_press corporate entity',
    'create oe_general corporate entity' => 'create oe_contact oe_general corporate entity',
    'edit oe_press corporate entity' => 'edit oe_contact oe_press corporate entity',
    'edit oe_general corporate entity' => 'edit oe_contact oe_general corporate entity',
    'delete oe_press corporate entity' => 'delete oe_contact oe_press corporate entity',
    'delete oe_general corporate entity' => 'delete oe_contact oe_general corporate entity',
    // Venue invalid and valid permissions.
    'create oe_default corporate entity' => 'create oe_venue oe_default corporate entity',
    'edit oe_default corporate entity' => 'edit oe_venue oe_default corporate entity',
    'delete oe_default corporate entity' => 'delete oe_venue oe_default corporate entity',
  ];

  /** @var \Drupal\user\Entity\Role $role */
  foreach (Role::loadMultiple() as $role) {
    $role_changed = FALSE;

    foreach ($permissions as $invalid => $valid) {
      if ($role->hasPermission($invalid)) {
        $role->revokePermission($invalid);
        $role->grantPermission($valid);
        $role_changed = TRUE;
      }
    }

    if ($role_changed) {
      $role->save();
    }
  }
}

/**
 * Ensure truncate maxlength setting is applied for textfields.
 */
function oe_content_post_update_00005(): void {
  foreach (array_keys(NodeType::loadMultiple()) as $content_type) {
    $display = EntityFormDisplay::load("node.$content_type.default");
    $changed = FALSE;
    foreach ($display->getComponents() as $field_name => $component) {
      if ((isset($component['type']) && $component['type'] !== 'string_textfield') || empty($component['third_party_settings']['maxlength'])) {
        continue;
      }
      $component['third_party_settings']['maxlength']['maxlength_js_enforce'] = TRUE;
      $display->setComponent($field_name, $component);
      $changed = TRUE;
    }

    if ($changed) {
      $display->save();
    }
  }
}
