<?php

declare(strict_types=1);

namespace Drupal\oe_content\Utility;

use Drupal\Component\Utility\NestedArray;

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
   *   Set to TRUE if both fields are supposed to be required.
   */
  public static function toggleFieldsWithCheckbox(array &$form, string $checkbox_field, string $field1, string $field2, $is_required = FALSE): void {
    $condition_checked = [':input[name="' . $checkbox_field . '[value]"]' => ['checked' => TRUE]];
    $condition_unchecked = [':input[name="' . $checkbox_field . '[value]"]' => ['checked' => FALSE]];

    // Set rules for visibility of elements.
    $form[$field1]['#states']['visible'] = $condition_checked;
    $form[$field2]['#states']['visible'] = $condition_unchecked;

    if ($is_required) {
      // Set rules if fields have to be required.
      $form[$field1]['#states']['required'] = $condition_checked;
      $form[$field2]['#states']['required'] = $condition_unchecked;
    }
  }

  /**
   * Toggle visibility of a field, depending on the value of another field.
   *
   * @param array $form
   *   The form array.
   * @param string $toggle_field
   *   The toggle field name.
   * @param string $dependent_field
   *   The dependent field name.
   * @param string $value
   *   The value that makes the dependent field visible.
   * @param array $dependent_parents
   *   An array of parents for the dependent field.
   */
  public static function toggleFieldVisibilityByValue(array &$form, string $toggle_field, string $dependent_field, string $value, array $dependent_parents = []): void {
    if (!isset($form[$dependent_field])) {
      return;
    }
    $parents_array = array_merge($dependent_parents, [$dependent_field]);
    if (!NestedArray::keyExists($form, $parents_array)) {
      return;
    }
    $states_array = array_merge($parents_array, ['#states']);
    NestedArray::setValue($form, $states_array, [
      'visible' => [
        ':input[name="' . $toggle_field . '"]' => [
          'value' => $value,
        ],
      ],
    ]);
  }

  /**
   * Toggle required state of a field, depending on the value of another field.
   *
   * @param array $form
   *   The form array.
   * @param string $toggle_field
   *   The toggle field name.
   * @param string $dependent_field
   *   The dependent field name.
   * @param string $value
   *   The value that makes the dependent field required.
   * @param array $dependent_parents
   *   An array of parents for the dependent field.
   */
  public static function toggleFieldRequiredByValue(array &$form, string $toggle_field, string $dependent_field, string $value, array $dependent_parents = []): void {
    $parents_array = array_merge($dependent_parents, [$dependent_field]);
    if (!NestedArray::keyExists($form, $parents_array)) {
      return;
    }
    $states_array = array_merge($parents_array, ['#states']);
    NestedArray::setValue($form, $states_array, [
      'required' => [
        ':input[name="' . $toggle_field . '"]' => [
          'value' => $value,
        ],
      ],
    ]);
  }

}
