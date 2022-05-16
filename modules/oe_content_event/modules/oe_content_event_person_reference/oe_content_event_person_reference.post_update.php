<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Event Person reference module.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_event_person_reference_post_update_00001(): void {
  $field_config = FieldConfig::load('oe_event_speaker.oe_default.oe_person');
  $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', FALSE);
  $field_config->save();
}
