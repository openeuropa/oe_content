<?php

/**
 * @file
 * Post update functions for OpenEuropa Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Utility\UpdateException;
use Drupal\field\Entity\FieldConfig;
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
 * Add new Author sub entity type with required fields.
 */
function oe_content_post_update_30005(): void {
  $entity_type_manager = \Drupal::entityTypeManager();
  // Install new 'Author' and 'Author type' entity types.
  $update_manager = \Drupal::entityDefinitionUpdateManager();
  $update_manager->installEntityType($entity_type_manager->getDefinition('oe_author'));
  $update_manager->installEntityType($entity_type_manager->getDefinition('oe_author_type'));

  $file_storage = new FileStorage(drupal_get_path('module', 'oe_content') . '/config/post_updates/30005_create_oe_author_entity_type');

  // Create Author bundle.
  $entity_type_manager->getStorage('oe_author_type')
    ->create(_oe_content_config_import_prepare($file_storage->read('oe_content.oe_author_type.oe_corporate_body')))
    ->save();

  // Create field for Author entity type.
  $field_storage_config = $entity_type_manager->getStorage('field_storage_config');
  $field_storage_config->create(_oe_content_config_import_prepare($file_storage->read('field.storage.oe_author.oe_skos_reference')))->save();
  $field_config = $entity_type_manager->getStorage('field_config');
  $field_config->create(_oe_content_config_import_prepare($file_storage->read('field.field.oe_author.oe_corporate_body.oe_skos_reference')))->save();

  // Configure entity form display.
  $entity_type_manager->getStorage('entity_form_display')->createFromStorageRecord(_oe_content_config_import_prepare($file_storage->read('core.entity_form_display.oe_author.oe_corporate_body.default')))->save();

  // Create field storage for nodes.
  $field_storage_config->create(_oe_content_config_import_prepare($file_storage->read('field.storage.node.oe_authors')))->save();

  // According to the fact that right now we can not control the order of
  // post_update hook implementation executions, we have to do the handling of
  // currently enabled content types in the main oe_content module.
  $content_types = $entity_type_manager->getStorage('node_type')->loadMultiple([
    'oe_event',
    'oe_news',
    'oe_page',
    'oe_policy',
    'oe_publication',
  ]);
  foreach (array_keys($content_types) as $content_type) {
    $field_config->create(_oe_content_config_import_prepare($file_storage->read('field.field.node.' . $content_type . '.oe_authors')))->save();
    $form_display = $file_storage->read('core.entity_form_display.node.' . $content_type . '.default');
    $entity = $entity_type_manager->getStorage('entity_form_display')->load($form_display['id']);
    $entity_type_manager->getStorage('entity_form_display')->updateFromStorageRecord($entity, _oe_content_config_import_prepare($form_display))->save();
  }
}
