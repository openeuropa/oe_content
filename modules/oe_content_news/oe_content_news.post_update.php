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
 * Create new field News types.
 */
function oe_content_news_post_update_00002(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_news') . '/config/post_updates/00002');
  $config_manager = \Drupal::service('config.manager');
  $entity_manager = \Drupal::entityTypeManager();

  $config_ids = [
    'field.storage.node.oe_news_types',
    'field.field.node.oe_news.oe_news_types',
  ];
  foreach ($config_ids as $config_id) {
    $config_record = $storage->read($config_id);
    $entity_type = $config_manager->getEntityTypeIdByName($config_id);
    $entity_storage = $entity_manager->getStorage($entity_type);
    $entity = $entity_storage->createFromStorageRecord($config_record);
    $entity->save();
  }
}

/**
 * Update form display.
 */
function oe_content_news_post_update_00003(): void {
  $storage = new FileStorage(drupal_get_path('module', 'oe_content_news') . '/config/post_updates/00003');
  /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
  $values = $storage->read('core.entity_form_display.node.oe_news.default');
  $display = EntityFormDisplay::load($values['id']);
  if ($display) {
    foreach ($values as $key => $value) {
      $display->set($key, $value);
    }
    $display->save();
  }
}
