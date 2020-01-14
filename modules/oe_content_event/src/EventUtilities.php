<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

/**
 * Provides generic event helper methods.
 */
class EventUtilities {

  /**
   * Helper method to apply the toggle states on two form fields.
   *
   * @param array $form
   *   The drupal form array.
   * @param string $toggle_field
   *   The toggle field name.
   * @param string $field1
   *   The dependent field name.
   * @param string $field2
   *   The dependent field name.
   */
  public static function applyToggleStatesToFormFields(array &$form, string $toggle_field, string $field1, string $field2): void {
    $form[$field1]['#states'] = [
      'visible' => [
        [
          ':input[name="' . $toggle_field . '[value]"]' => ['checked' => TRUE],
        ],
      ],
      'required' => [
        [
          ':input[name="' . $toggle_field . '[value]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form[$field2]['#states'] = [
      'visible' => [
        [
          ':input[name="' . $toggle_field . '[value]"]' => ['checked' => FALSE],
        ],
      ],
      'required' => [
        [
          ':input[name="' . $toggle_field . '[value]"]' => ['checked' => FALSE],
        ],
      ],
    ];
  }

}
