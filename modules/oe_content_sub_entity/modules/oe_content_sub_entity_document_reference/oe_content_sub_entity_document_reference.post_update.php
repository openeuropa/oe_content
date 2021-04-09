<?php

/**
 * @file
 * Post update functions for Content Sub Entity Document reference module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;

/**
 * Hide untranslatable fields for all bundles.
 *
 * There is a known problem with entity reference revision fields
 * being used on entities with workflow enabled that prevents
 * the referenced entity from being saved. This problem can be avoided
 * by simply hiding untranslatable fields.
 * See more info at:
 * https://www.drupal.org/project/entity_reference_revisions/issues/3150084
 */
function oe_content_sub_entity_document_reference_post_update_00001() {
  $file_storage = new FileStorage(drupal_get_path('module', 'oe_content_sub_entity_document_reference') . '/config/post_updates/00001_hide_untranslatable_fields');
  $storage = \Drupal::entityTypeManager()->getStorage('language_content_settings');

  $config_ids = [
    'language.content_settings.oe_document_reference.oe_document',
    'language.content_settings.oe_document_reference.oe_publication',
  ];
  foreach ($config_ids as $config_id) {
    $values = $file_storage->read($config_id);
    $language_content_settings = $storage->load($values['id']);
    if ($language_content_settings) {
      $storage->updateFromStorageRecord($language_content_settings, $values);
      $language_content_settings->save();
    }
  }

}
