<?php

/**
 * @file
 * Post update functions for Content Sub Entity Document reference module.
 */

declare(strict_types = 1);

use Drupal\Core\Config\FileStorage;
use Drupal\field\Entity\FieldConfig;

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
  $file_storage = new FileStorage(\Drupal::service('extension.list.module')->getPath('oe_content_sub_entity_document_reference') . '/config/post_updates/00001_hide_untranslatable_fields');
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

/**
 * Set "composite revisions" option for reference fields.
 */
function oe_content_sub_entity_document_reference_post_update_00002(): void {
  $fields = [
    'oe_document_reference.oe_document.oe_document' => FALSE,
    'oe_document_reference.oe_publication.oe_publication' => FALSE,
  ];
  foreach ($fields as $field => $value) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', $value);
    $field_config->save();
  }
}

/**
 * Enables the publication document reference module.
 */
function oe_content_sub_entity_document_reference_post_update_00003(): void {
  // The module contains the config that was hosted in this module in the past.
  // For consistency, we enable the module so that new installs and updates are
  // in the same situation.
  \Drupal::service('module_installer')->install(['oe_content_publication_document_reference']);
}
