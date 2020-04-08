<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Utility;

/**
 * Provides helper methods to manipulate content forms.
 */
class ContentFormUtilities {

  /**
   * Toggle visibility of two fields, depending on the state of a checkbox.
   *
   * @param array $form
   *   The form array.
   * @param string $checkbox_field
   *   The toggle field name.
   * @param string $field1
   *   The first dependent field name.
   * @param string $field2
   *   The second dependent field name.
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
