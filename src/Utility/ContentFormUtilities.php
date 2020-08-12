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
   * @param bool $is_required
   *   Does the field 1 and field 2 have to be required ?
   */
  public static function toggleFieldsWithCheckbox(array &$form, string $checkbox_field, string $field1, string $field2, $is_required = TRUE): void {
    $condition_checked = [':input[name="' . $checkbox_field . '[value]"]' => ['checked' => TRUE]];
    $condition_unchecked = [':input[name="' . $checkbox_field . '[value]"]' => ['checked' => FALSE]];

    $form[$field1]['#states']['visible'] = $condition_checked;
    if ($is_required) {
      $form[$field1]['#states']['required'] = $condition_checked;
    }

    $form[$field2]['#states']['visible'] = $condition_unchecked;
    if ($is_required) {
      $form[$field2]['#states']['required'] = $condition_unchecked;
    }
  }

}
