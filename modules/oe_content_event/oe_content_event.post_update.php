<?php

/**
 * @file
 * Post update functions for OpenEuropa Event Content module.
 */

declare(strict_types = 1);

use Drupal\field\Entity\FieldConfig;

/**
 * Make the Event venue and contact fields composite.
 */
function oe_content_event_post_update_00001(array &$sandbox) {
  \Drupal::service('module_installer')->install(['composite_reference']);

  $fields = [
    'node.oe_event.oe_event_venue',
    'node.oe_event.oe_event_contact',
  ];
  foreach ($fields as $field) {
    $field_config = FieldConfig::load($field);
    $field_config->setThirdPartySetting('composite_reference', 'composite', TRUE);
    $field_config->save();
  }
}

/**
 * Make the Event venue not required.
 */
function oe_content_event_post_update_00002(array &$sandbox) {
  $field_config = FieldConfig::load('node.oe_event.oe_event_venue');
  $field_config->set('required', FALSE);
  $field_config->save();
}
