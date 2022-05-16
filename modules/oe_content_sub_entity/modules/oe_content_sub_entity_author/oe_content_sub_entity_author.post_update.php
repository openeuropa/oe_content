<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Sub Entity Author module.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

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
