<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Element\NodeElement;
use PHPUnit\Framework\Assert;

/**
 * Helper methods for interacting with CKEditor5 WYSIWYG editors.
 */
trait WysiwygTrait {

  /**
   * Checks whether a WYSIWYG editor with the given field label is present.
   *
   * @param string $field
   *   The label of the field to which the WYSIWYG editor is attached.
   *
   * @return bool
   *   TRUE if the editor is present, FALSE otherwise.
   */
  public function hasWysiwyg(string $field) {
    try {
      $this->getWysiwyg($field);
      return TRUE;
    }
      // Only catch the specific exception that thrown when the WYSIWYG editor
      // is not present, let all other exceptions pass through.
    catch (\Exception $e) {
      return FALSE;
    }
  }

  /**
   * Presses the given WYSIWYG button.
   *
   * @param string $field
   *   The field label of the field to which the WYSIWYG editor is attached. For
   *   example 'Body'.
   * @param string $button_label
   *   The label of the button to click.
   */
  public function pressWysiwygButton(string $field, string $button_label): void {
    $wysiwyg = $this->getWysiwyg($field);

    // Try to see if there is a dropdown button to reveal the button.
    $dropdown_button = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="Show more items"]');
    if (!empty($dropdown_button)) {
      $dropdown_button = reset($dropdown_button);
      $dropdown_button->click();
    }

    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="' . $button_label . '"][1]');
    Assert::assertNotEmpty($button_elements, "Could not find the '$button_label' button.");
    Assert::assertCount(1, $button_elements, "Multiple '$button_label' buttons found in the editor.");

    $button_element = reset($button_elements);
    $button_element->click();
  }

  /**
   * Enters the given text in the textarea of the specified WYSIWYG editor.
   *
   * If there is any text existing it will be replaced.
   *
   * @param string $field
   *   The field label of the field to which the WYSIWYG editor is attached. For
   *   example 'Body'.
   * @param string $text
   *   The text to enter in the textarea.
   */
  public function setWysiwygText(string $field, string $text): void {
    $ckeditor5_id = $this->getCKEditor5Id($field);
    $javascript = <<<JS
(function(){
  return Drupal.CKEditor5Instances.get('$ckeditor5_id').setData(`$text`);
})();
JS;
    $this->getSession()->evaluateScript($javascript);
  }

  /**
   * Returns the WYSIWYG editor that is associated with the given field label.
   *
   * This is hardcoded on the CKE editor which is included with Drupal core.
   *
   * @param string $field
   *   The label of the field to which the WYSIWYG editor is attached.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The WYSIWYG editor.
   */
  public function getWysiwyg(string $field): NodeElement {
    $driver = $this->getSession()->getDriver();
    $label_elements = $driver->find('//label[text()="' . $field . '"]');
    Assert::assertNotEmpty($label_elements, "Could not find the '$field' field label.");
    Assert::assertCount(1, $label_elements, "Multiple '$field' labels found in the page.");

    $wysiwyg_elements = $driver->find('//label[contains(text(), "' . $field . '")]/following::div[contains(@class, " ck-editor ")][1]');
    Assert::assertNotEmpty($wysiwyg_elements, "Could not find the '$field' wysiwyg editor.");
    Assert::assertCount(1, $wysiwyg_elements, "Multiple '$field' wysiwyg editors found in the page.");

    return reset($wysiwyg_elements);
  }

  /**
   * Enters the given text in the given WYSIWYG editor.
   *
   * If this is running on a JavaScript enabled browser it execute
   * a JS code to enter the text into CKEditor.
   *
   * @param string $label
   *   The label of the field containing the WYSIWYG editor.
   * @param string $text
   *   The text to enter in the WYSIWYG editor.
   */
  protected function enterTextInWysiwyg(string $label, string $text): void {
    if ($this->browserSupportsJavaScript()) {
      $this->setWysiwygText($label, $text);
    }
    else {
      $this->getSession()->getPage()->fillField($label, $text);
    }
  }

  /**
   * Gets the "data-ckeditor5-id" attribute value.
   *
   * @param string $label
   *   The label of the WYSIWYG field to look at.
   *
   * @return string|int
   *   Returns the "data-ckeditor5-id" attribute value.
   */
  protected function getCKEditor5Id(string $label): string|int {
    $wysiwyg = $this->getWysiwyg($label);
    $textarea = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '/preceding-sibling::textarea');
    Assert::assertNotEmpty($textarea, "Could not find the '$label' textarea element.");

    $textarea = reset($textarea);
    $ckeditor_id = $textarea->getAttribute('data-ckeditor5-id');
    Assert::assertNotEmpty($ckeditor_id, "Could not find the '$label' textarea element's ckeditor5 id.");

    return $ckeditor_id;
  }

}
