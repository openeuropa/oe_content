<?php

/**
 * @file
 * OpenEuropa Consultation Content post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Enable "composite revisions" option for "Contact" and "Documents" fields.
 */
function oe_content_consultation_post_update_00001(): void {
  $fields = [
    'node.oe_consultation.oe_consultation_contacts',
    'node.oe_consultation.oe_consultation_documents',
  ];
  foreach ($fields as $id) {
    $field_config = FieldConfig::load($id);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', TRUE);
    $field_config->save();
  }
}
