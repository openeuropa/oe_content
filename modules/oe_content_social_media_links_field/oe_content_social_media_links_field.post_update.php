<?php

/**
 * @file
 * OpenEuropa Content Social Media Links Field post updates.
 */

declare(strict_types=1);

/**
 * Add Telegram and Mastodon options to social media links field.
 */
function oe_content_social_media_links_field_post_update_00001(): void {
  $field_storage = \Drupal::entityTypeManager()->getStorage('field_storage_config')->load('node.oe_social_media_links');
  if (!$field_storage) {
    return;
  }
  $settings = $field_storage->get('settings');
  $settings['allowed_values']['telegram'] = 'Telegram';
  $settings['allowed_values']['mastodon'] = 'Mastodon';
  $field_storage->set('settings', $settings);
  $field_storage->save();
}

/**
 * Update Twitter label to X.
 */
function oe_content_social_media_links_field_post_update_00002() {
  $field_storage = \Drupal::entityTypeManager()->getStorage('field_storage_config')->load('node.oe_social_media_links');
  $settings = $field_storage->get('settings');
  if (!isset($settings['allowed_values']['twitter'])) {
    return 'The field storage does not contain the twitter key.';
  }
  if ($settings['allowed_values']['twitter'] !== 'Twitter') {
    return 'The label of the twitter key is different than the original value. No update required.';
  }
  $settings['allowed_values']['twitter'] = 'X';
  $field_storage->set('settings', $settings);
  $field_storage->save();
}
