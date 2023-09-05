<?php

/**
 * @file
 * OpenEuropa Content Sub Entity Person post updates.
 */

declare(strict_types = 1);

/**
 * Enables the Person sub-entity reference and SKOS person reference modules.
 */
function oe_content_sub_entity_person_post_update_00001(): void {
  // The modules contain the config that was hosted in this module in the past.
  // For consistency, we enable the modules so that new installs and updates are
  // in the same situation.
  $modules = [
    'oe_content_person_sub_entity_reference',
    'oe_content_skos_person_reference',
  ];
  \Drupal::service('module_installer')->install($modules);
}
