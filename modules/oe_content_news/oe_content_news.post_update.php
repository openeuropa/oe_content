<?php

/**
 * @file
 * OpenEuropa News post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;

/**
 * Update body and summary labels.
 */
function oe_content_news_post_update_00001_update_field_labels(array &$sandbox): void {
  $new_field_labels = [
    'node.oe_news.oe_summary' => 'Introduction',
    'node.oe_news.body' => 'Body text',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }
}

/**
 * Enable new modules from dependency.
 */
function oe_content_news_post_update_00002(): void {
  $modules = [
    'field_group',
    'oe_content_reference_code_field',
    'oe_content_entity_contact',
    'composite_reference',
    'entity_reference_revisions',
    'inline_entity_form',
  ];
  \Drupal::service('module_installer')->install($modules);
}

/**
 * Create new fields in the news content type.
 *
 * Add fields oe_reference, oe_news_types and oe_news_contacts.
 */
function oe_content_news_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_news') . '/config/post_updates/00003_create_fields');
  \Drupal::service('config.installer')->installOptionalConfig($storage);
}

/**
 * Update news node form display.
 */
function oe_content_news_post_update_00004(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_news') . '/config/post_updates/00004_update_form_display');

  // Form display configurations to update.
  $form_display_values = $storage->read('core.entity_form_display.node.oe_news.default');
  $form_display = EntityFormDisplay::load($form_display_values['id']);
  if ($form_display) {
    $updated_form_display = \Drupal::entityTypeManager()
      ->getStorage($form_display->getEntityTypeId())
      ->updateFromStorageRecord($form_display, $form_display_values);
    $updated_form_display->save();
  }
}

/**
 * Change publication date widget to select list.
 */
function oe_content_news_post_update_00005(): void {
  $form_display = EntityFormDisplay::load('node.oe_news.default');
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

/**
 * Set inline entity form widgets reference removal policy to keep entities.
 */
function oe_content_news_post_update_00006(): void {
  $form_display = EntityFormDisplay::load('node.oe_news.default');
  $component = $form_display->getComponent('oe_news_contacts');
  $component['settings']['removed_reference'] = 'keep';
  $form_display->setComponent('oe_news_contacts', $component)->save();
}

/**
 * Set body field optional.
 */
function oe_content_news_post_update_00007(): void {
  $field_config = FieldConfig::load('node.oe_news.body');
  $field_config->setRequired(FALSE);
  $field_config->save();
}
