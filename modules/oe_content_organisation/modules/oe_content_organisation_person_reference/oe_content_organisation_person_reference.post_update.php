<?php

/**
 * @file
 * Post update functions for OpenEuropa Organisation Person Reference module.
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_organisation_person_reference_post_update_00001(): void {
  $field_config = FieldConfig::load('node.oe_organisation.oe_organisation_persons');
  $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', FALSE);
  $field_config->save();
}
