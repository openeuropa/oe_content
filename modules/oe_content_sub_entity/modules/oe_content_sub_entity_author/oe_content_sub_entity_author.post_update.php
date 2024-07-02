<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Sub Entity Author module.
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;
use Drupal\oe_content\ConfigImporter;
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

  $extension = 'oe_content_sub_entity_author';
  $config_path = '/config/post_updates/00002_political_leader';
  $configs_to_import = [
    'field.field.oe_author.oe_political_leader.oe_skos_reference',
    'core.entity_form_display.oe_author.oe_political_leader.default',
    'core.entity_view_display.oe_author.oe_political_leader.default',
  ];

  ConfigImporter::importMultiple('module', $extension, $config_path, $configs_to_import);

  if (\Drupal::moduleHandler()->moduleExists('content_translation')) {
    ConfigImporter::importSingle('module', $extension, $config_path, 'language.content_settings.oe_author.oe_political_leader');
  }
}
