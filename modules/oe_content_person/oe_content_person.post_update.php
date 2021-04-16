<?php

/**
 * @file
 * Post update functions for OpenEuropa Person Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;

/**
 * Add length restrictions for title display fields.
 */
function oe_content_person_post_update_20001(): void {
  // Update form display to add maxlength to first and last names.
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_person') . '/config/post_updates/20001_person_title');
  $entity_type_manager = \Drupal::entityTypeManager();
  $display_values = $storage->read('core.entity_form_display.node.oe_person.default');
  $form_display_storage = $entity_type_manager->getStorage('entity_form_display');
  $existing_display = $form_display_storage->load($display_values['id']);
  if ($existing_display) {
    $updated_display = $form_display_storage->updateFromStorageRecord($existing_display, $display_values);
    $updated_display->save();
  }

  // Update display name storage to match the title max length.
  $field_storage = $entity_type_manager->getStorage('field_storage_config')->load('node.oe_person_displayed_name');
  $settings = $field_storage->get('settings');
  $settings['max_length'] = '255';
  $field_storage->set('settings', $settings);
  $field_storage->save();
}

/**
 * Use role qualifier vocabulary on person job role.
 */
function oe_content_person_post_update_20002(): void {
  // Add the new role qualifier vocabulary.
  $graphs = [
    'role-qualifier' => 'http://publications.europa.eu/resource/dataset/role-qualifier',
  ];
  \Drupal::service('rdf_skos.skos_graph_configurator')->addGraphs($graphs);

  // Use the new vocabulary on the role reference field.
  $entity_type_manager = \Drupal::entityTypeManager();
  $field_storage = $entity_type_manager->getStorage('field_config')->load('oe_person_job.oe_default.oe_role_reference');
  $settings = $field_storage->get('settings');
  $settings['handler_settings']['concept_schemes'] = ['http://publications.europa.eu/resource/authority/role-qualifier'];
  $settings['handler_settings']['field']['concept_schemes'] = ['http://publications.europa.eu/resource/authority/role-qualifier'];
  $field_storage->set('settings', $settings);
  $field_storage->save();
}
