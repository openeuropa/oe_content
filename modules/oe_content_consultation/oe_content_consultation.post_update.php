<?php

/**
 * @file
 * OpenEuropa Consultation Content post updates.
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_consultation_post_update_00001(): void {
  $fields = [
    'node.oe_consultation.oe_consultation_contacts' => TRUE,
    'node.oe_consultation.oe_consultation_documents' => TRUE,
    'node.oe_consultation.oe_consultation_outcome_files' => FALSE,
  ];
  foreach ($fields as $field => $value) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', $value);
    $field_config->save();
  }
}
