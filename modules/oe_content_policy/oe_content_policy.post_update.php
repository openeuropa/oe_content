<?php

/**
 * @file
 * OpenEuropa Content Policy post updates.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Add timeline field to policy content type.
 */
function oe_content_policy_post_update_00001_timeline_field(): void {
  // Clear field type plugin cache.
  \Drupal::service('plugin.manager.field.field_type')->clearCachedDefinitions();

  if (!\Drupal::service('entity_type.manager')->getStorage('field_storage_config')->load('node.oe_timeline')) {
    FieldStorageConfig::create([
      'field_name' => 'oe_timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
      'cardinality' => -1,
      'entity_types' => ['node'],
    ])->save();
  }

  if (!\Drupal::service('entity_type.manager')->getStorage('field_config')->load('node.oe_policy.oe_timeline')) {
    FieldConfig::create([
      'label' => 'Timeline',
      'field_name' => 'oe_timeline',
      'entity_type' => 'node',
      'bundle' => 'oe_policy',
      'settings' => [],
      'required' => FALSE,
    ])->save();
  }
}

/**
 * Update body and summary labels.
 */
function oe_content_policy_post_update_00002_field_labels(array &$sandbox): void {
  $new_field_labels = [
    'node.oe_policy.oe_summary' => 'Introduction',
    'node.oe_policy.body' => 'Body text',
  ];
  foreach ($new_field_labels as $id => $new_label) {
    $field_config = FieldConfig::load($id);
    $field_config->setLabel($new_label);
    $field_config->save();
  }
}

/**
 * Update title, teaser, summary and subject fields description.
 */
function oe_content_policy_post_update_00003(array &$sandbox): void {
  $fields_description = [
    'node.oe_policy.title' => 'The ideal length is 50 to 60 characters including spaces. If it must be longer, make sure you fill in a shorter version in the Alternative title field',
    'node.oe_policy.oe_subject' => 'The topics mentioned on this page. These will be used by search engines and dynamic lists to determine their relevance to a user.',
    'node.oe_policy.oe_summary' => 'A short text that will be displayed in the blue header, below the page title. This should be a brief summary of the content on the page that tells the user what information they will find on this page.',
    'node.oe_policy.oe_teaser' => 'A short overview of the information on this page. The teaser will be displayed in list views and search engine results, not on the page itself. Limited to 150 characters for SEO purposes.',
  ];

  foreach ($fields_description as $id => $description) {
    $field_config = FieldConfig::load($id);
    if ($id == 'node.oe_policy.title') {
      $field_config->setLabel('Page title');
    }
    $field_config->setDescription($description);
    $field_config->save();
  }
}
