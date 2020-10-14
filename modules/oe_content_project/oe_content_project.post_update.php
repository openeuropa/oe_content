<?php

/**
 * @file
 * Post update functions for OpenEuropa Project Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;

/**
 * Set inline entity form widget reference removal policy to keep entities.
 */
function oe_content_project_post_update_00001(): void {
  $form_display = EntityFormDisplay::load('node.oe_project.default');

  foreach (['oe_project_contact', 'oe_project_coordinators', 'oe_project_participants'] as $field_name) {
    $component = $form_display->getComponent($field_name);
    $component['settings']['removed_reference'] = 'keep';
    $form_display->setComponent($field_name, $component);
  }

  $form_display->save();
}
