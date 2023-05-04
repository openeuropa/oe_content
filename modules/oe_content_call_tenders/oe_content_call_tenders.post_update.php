<?php

/**
 * @file
 * Post update functions for OpenEuropa Call for tenders Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;

/**
 * Add maxlegnth to oe_content_short_title, oe_summary, oe_teaser, title.
 */
function oe_content_call_tenders_post_update_00001() {
  $file_storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_call_tenders') . '/config/post_updates/00001_add_maxlength');
  $storage = \Drupal::entityTypeManager()->getStorage('entity_form_display');

  $display_id = 'core.entity_form_display.node.oe_call_tenders.default';
  $values = $file_storage->read($display_id);
  /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $form_display */
  $form_display = $storage->load($values['id']);

  if ($form_display) {
    $storage->updateFromStorageRecord($form_display, $values);
    $form_display->save();
  }
}

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_call_tenders_post_update_00002(): void {
  $field_config = FieldConfig::load('node.oe_call_tenders.oe_documents');
  $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', FALSE);
  $field_config->save();
}
