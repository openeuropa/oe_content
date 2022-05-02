<?php

/**
 * @file
 * OpenEuropa Call for proposals post updates.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;

/**
 * Set inline entity form widgets reference removal policy to keep entities.
 */
function oe_content_call_proposals_post_update_00001(): void {
  $form_display = EntityFormDisplay::load('node.oe_call_proposals.default');
  $component = $form_display->getComponent('oe_call_proposals_contact');
  $component['settings']['removed_reference'] = 'keep';
  $form_display->setComponent('oe_call_proposals_contact', $component)->save();
}

/**
 * Enable "composite revisions" option for "Contact" field.
 */
function oe_content_call_proposals_post_update_00002(): void {
  $field_config = FieldConfig::load('node.oe_call_proposals.oe_call_proposals_contact');
  $field_config->setThirdPartySetting('composite_reference', 'composite_revisions', TRUE);
  $field_config->save();
}
