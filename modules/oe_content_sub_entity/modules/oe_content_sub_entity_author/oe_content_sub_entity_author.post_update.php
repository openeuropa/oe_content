<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Sub Entity Author module.
 */

declare(strict_types = 1);

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;
use Drupal\oe_content_sub_entity_author\Entity\AuthorType;

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_sub_entity_author_post_update_00001(): void {
  $fields = [
    'oe_author.oe_organisation.oe_node_reference' => FALSE,
    'oe_author.oe_person.oe_node_reference' => FALSE,
  ];
  foreach ($fields as $field => $value) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', $value);
    $field_config->save();
  }
}

/**
 * Install EU Political leader author type and graph.
 */
function oe_content_sub_entity_author_post_update_00002(): void {
  // Add 'EU political leader name' vocabulary.
  \Drupal::service('rdf_skos.skos_graph_configurator')->addGraphs([
    'political-leader' => 'http://publications.europa.eu/resource/dataset/political-leader',
  ]);

  $political_leader = AuthorType::create([
    'id' => 'oe_political_leader',
    'label' => 'EU Political leader',
  ]);
  $political_leader->save();

  $entity_type_manager = \Drupal::entityTypeManager();
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_sub_entity_author') . '/config/post_updates/00002_political_leader');
  $configs_to_import = [
    'field.field.oe_author.oe_political_leader.oe_skos_reference',
    'core.entity_form_display.oe_author.oe_political_leader.default',
    'core.entity_view_display.oe_author.oe_political_leader.default',
  ];

  // Function to import a single config from the file storage, given the name.
  $import_single_config = function (string $name) use ($storage, $entity_type_manager) {
    $config = $storage->read($name);

    $entity_type = \Drupal::service('config.manager')->getEntityTypeIdByName($name);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
    $entity_storage = $entity_type_manager->getStorage($entity_type);

    $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
    $entity = $entity_storage->createFromStorageRecord($config);
    $entity->save();
  };

  foreach ($configs_to_import as $name) {
    $import_single_config($name);
  }

  if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
    $import_single_config('language.content_settings.oe_author.oe_political_leader');
  }
}
