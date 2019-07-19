<?php

/**
 * @file
 * OpenEuropa Page post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Update body and summary labels.
 */
function oe_content_page_post_update_8100_update_field_labels(array &$sandbox): void {
  $new_field_labels = [
    'node.oe_page.oe_summary' => 'Introduction',
    'node.oe_page.body' => 'Body text',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }
}
