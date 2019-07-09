<?php

/**
 * @file
 * OpenEuropa Content post updates.
 */

declare(strict_types = 1);

/**
 * Set a max length for title fields on corporate content types.
 */
function oe_content_post_update_set_title_max_length(array &$sandbox): void {
  $target_bundles = [
    'oe_news',
    'oe_page',
    'oe_policy',
    'oe_publication',
  ];
  $available_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('node');

  foreach ($target_bundles as $target_bundle) {
    if (array_key_exists($target_bundle, $available_bundles)) {
      $properties = [
        'targetEntityType' => 'node',
        'bundle' => $target_bundle,
      ];
      if ($form_displays = \Drupal::entityTypeManager()->getStorage('entity_form_display')->loadByProperties($properties)) {
        /** @var \Drupal\Core\Entity\Entity\EntityFormDisplay $form_display */
        foreach ($form_displays as $form_display) {
          if ($component = $form_display->getComponent('title')) {
            if ($component['type'] == 'string_textfield') {
              $component['third_party_settings']['maxlength'] = [
                'maxlength_js' => '255',
                'maxlength_js_label' => 'Content limited to @limit characters, remaining: <strong>@remaining</strong>',
              ];
            }
            $form_display->setComponent('title', $component)->save();
          }
        }
      }
    }
  }
}
