<?php

/**
 * @file
 * OpenEuropa Publication post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
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
 * Enable oe_content_documents_field module.
 */
function oe_content_publication_post_update_00002() {
  \Drupal::service('module_installer')->install(['oe_content_documents_field']);
}

/**
 * Change publication date widget to select list.
 */
function oe_content_publication_post_update_00005(): void {
  $form_display = EntityFormDisplay::load('node.oe_publication.default');
  $content = $form_display->get('content') ?: [];
  if (!isset($content['oe_publication_date'])) {
    return;
  }

  $content['oe_publication_date']['type'] = 'datetime_datelist';
  $content['oe_publication_date']['settings'] = [
    'date_order' => 'DMY',
    'time_type' => 'none',
    'increment' => 15,
  ];
  $form_display->set('content', $content);
  $form_display->save();
}
