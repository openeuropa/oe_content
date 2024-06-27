<?php

/**
 * @file
 * Post update functions for OpenEuropa Project Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Set inline entity form widget reference removal policy to keep entities.
 */
function oe_content_project_post_update_00001(): void {
  $form_display = EntityFormDisplay::load('node.oe_project.default');

  $fields = [
    'oe_project_contact',
    'oe_project_coordinators',
    'oe_project_participants',
  ];

  foreach ($fields as $field_name) {
    $component = $form_display->getComponent($field_name);
    $component['settings']['removed_reference'] = 'keep';
    $form_display->setComponent($field_name, $component);
  }

  $form_display->save();
}

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_project_post_update_00002(): void {
  $fields = [
    'node.oe_project.oe_documents' => FALSE,
    'node.oe_project.oe_project_contact' => TRUE,
    'node.oe_project.oe_project_coordinators' => TRUE,
    'node.oe_project.oe_project_participants' => TRUE,
  ];
  foreach ($fields as $field => $value) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', $value);
    $field_config->save();
  }
}

/**
 * Add decimal budget fields and deprecate old fields.
 */
function oe_content_project_post_update_30001(): void {
  $storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_project') . '/config/post_updates/30001_decimal_budget_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);

  // Add deprecated to the old budget field label.
  $fields = [
    'node.oe_project.oe_project_budget' => 'EU contribution (deprecated)',
    'node.oe_project.oe_project_budget_eu' => 'Overall budget (deprecated)',
  ];
  foreach ($fields as $field => $label) {
    $field_config = FieldConfig::load($field);
    $field_config->setLabel($label);
    $field_config->save();
  }
}

/**
 * Remove old budget fields after 4.x.
 */
function oe_content_project_post_update_40001(): void {
  $fields_to_remove = [
    'oe_project_budget',
    'oe_project_budget_eu',
  ];
  foreach ($fields_to_remove as $field_name) {
    if ($field_storage = FieldStorageConfig::load("node.$field_name")) {
      $field_storage->delete();
    }
  }
}
