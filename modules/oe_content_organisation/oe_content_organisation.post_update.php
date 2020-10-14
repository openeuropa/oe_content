<?php

/**
 * @file
 * Post update functions for OpenEuropa Organisation Content module.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;

/**
 * Set inline entity form widgets reference removal policy to keep entities.
 */
function oe_content_organisation_post_update_00001(): void {
  $form_display = EntityFormDisplay::load('node.oe_organisation.default');
  $component = $form_display->getComponent('oe_organisation_contact');
  $component['settings']['removed_reference'] = 'keep';
  $form_display->setComponent('oe_organisation_contact', $component)->save();
}
