<?php

/**
 * @file
 * OpenEuropa Organisation post updates.
 */

declare(strict_types = 1);

/**
 * Update contact IEF settings to prevent revisions from being created.
 */
function oe_content_organisation_post_update_00001(): void {
  $storage = \Drupal::service('entity_type.manager')->getStorage('entity_form_display');
  /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $entity_form_display */
  $entity_form_display = $storage->load('node.oe_organisation.default');
  $content_settings = $entity_form_display->get('content');
  if (!isset($content_settings['oe_organisation_contact']) || $content_settings['oe_organisation_contact']['type'] !== 'inline_entity_form_complex') {
    return;
  }
  $content_settings['oe_organisation_contact']['settings']['revision'] = FALSE;
  $entity_form_display->set('content', $content_settings)->save();
}
