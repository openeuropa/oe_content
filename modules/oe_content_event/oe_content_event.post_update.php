<?php

/**
 * @file
 * Post update functions for OpenEuropa Content Event module.
 */

declare(strict_types = 1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;

/**
 * Change widget for "Event type" field to skos select in form display.
 */
function oe_content_event_post_update_00001() {
  $display = EntityFormDisplay::load('node.oe_event.default');
  $content = $display->get('content');

  if (empty($content['oe_event_type'])) {
    return 'Event type field is not exposed on the form display.';
  }

  $content['oe_event_type']['type'] = 'skos_concept_entity_reference_options_select';
  $content['oe_event_type']['settings'] = [];
  $display->set('content', $content);
  $display->save();
}
