<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Update body and summary labels.
 */
function oe_content_publication_post_update_00001_update_field_labels(array &$sandbox): void {
  $new_field_labels = [
    'node.oe_publication.oe_summary' => 'Introduction',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }
}

/**
 * Make RDF fields not translatable.
 */
function oe_content_publication_post_update_00002_not_translatable_rdf_field(array &$sandbox): void {
  $field_ids = [
    'node.oe_publication.oe_author',
    'node.oe_publication.oe_publication_type',
    'node.oe_publication.oe_subject',
  ];
  foreach ($field_ids as $field_id) {
    $field_config = FieldConfig::load($field_id);
    $field_config->setTranslatable(FALSE);
    $field_config->save();
  }
}
