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
 * Enable "composite revisions" option for fields in Project CT.
 *
 * Updated fields: "Project contact", "Coordinators", "Participants".
 */
function oe_content_project_post_update_00002(): void {
  $fields = [
    'node.oe_project.oe_project_contact',
    'node.oe_project.oe_project_coordinators',
    'node.oe_project.oe_project_participants',
  ];
  foreach ($fields as $id) {
    $field_config = FieldConfig::load($id);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', TRUE);
    $field_config->save();
  }
}
