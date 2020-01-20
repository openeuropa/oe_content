<?php

/**
 * @file
 * OpenEuropa News post updates.
 */

declare(strict_types = 1);

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
 * Update title, teaser, summary and subject fields description.
 */
function oe_content_news_post_update_00002(array &$sandbox): void {
  // Update teaser, summary and subject.
  $fields_description = [
    'node.oe_news.oe_subject' => 'The topics mentioned on this page. These will be used by search engines and dynamic lists to determine their relevance to a user.',
    'node.oe_news.oe_summary' => 'A short text that will be displayed in the blue header, below the page title. This should be a brief summary of the content on the page that tells the user what information they will find on this page.',
    'node.oe_news.oe_teaser' => 'A short overview of the information on this page. The teaser will be displayed in list views and search engine results, not on the page itself. Limited to 150 characters for SEO purposes.',
  ];

  foreach ($fields_description as $id => $description) {
    $field_config = FieldConfig::load($id);
    $field_config->setDescription($description);
    $field_config->save();
  }

  // Update title base field.
  $fields = \Drupal::service('entity_field.manager')->getBaseFieldDefinitions('node', 'oe_news');
  $field_config = $fields['title']->getConfig('oe_news');
  $field_config->setLabel('Page title');
  $field_config->setDescription('The ideal length is 50 to 60 characters including spaces. If it must be longer, make sure you fill in a shorter version in the Alternative title field.');
  $field_config->save();
}
