<?php

/**
 * @file
 * Post update functions for OpenEuropa Project Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;

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
