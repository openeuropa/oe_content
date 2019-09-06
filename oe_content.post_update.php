<?php

/**
 * @file
 * Post update functions for OpenEuropa Content module.
 */

declare(strict_types = 1);

use Drupal\media\Entity\MediaType;

/**
 * Remove the mapping of name to title on documents and image media types.
 */
function oe_content_post_update_00001(): void {
  $media_types = ['document', 'image'];
  foreach ($media_types as $media_type_id) {
    /** @var \Drupal\media\Entity\MediaType $media_type */
    $media_type = MediaType::load($media_type_id);
    $field_mappings = $media_type->get('field_map');
    if (isset($field_mappings['name']) && $field_mappings['name'] == 'name') {
      unset($field_mappings['name']);
    }
    $media_type->set('field_map', $field_mappings);
    $media_type->save();
  }
}
