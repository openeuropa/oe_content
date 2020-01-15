<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event\Utilities;

/**
 * Provides helper methods to manipulate content forms.
 */
class ContentFormUtilities {

  /**
   * Helper method to apply the toggle states on two form fields.
   *
   * @param array $form
   *   The drupal form array.
   * @param string $checkbox_field
   *   The toggle field name.
   * @param string $field1
   *   The dependent field name.
   * @param string $field2
   *   The dependent field name.
   */
  public static function toggleFieldsWithCheckbox(array &$form, string $checkbox_field, string $field1, string $field2): void {
    $form[$field1]['#states'] = [
      'visible' => [
        [
          ':input[name="' . $checkbox_field . '[value]"]' => ['checked' => TRUE],
        ],
      ],
      'required' => [
        [
          ':input[name="' . $checkbox_field . '[value]"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form[$field2]['#states'] = [
      'visible' => [
        [
          ':input[name="' . $checkbox_field . '[value]"]' => ['checked' => FALSE],
        ],
      ],
      'required' => [
        [
          ':input[name="' . $checkbox_field . '[value]"]' => ['checked' => FALSE],
        ],
      ],
    ];
  }

}
