<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Element\NodeElement;

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
      $button = reset($dropdown_button);
      $button->click();
    }

    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="' . $button_label . '"]');
    if (empty($button_elements)) {
      throw new \Exception("Could not find the '$button_label' button.");
    }

    if (count($button_elements) > 1) {
      throw new \Exception("Multiple '$button_label' buttons found in the editor.");
    }
    $button = reset($button_elements);
    $button->click();
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
    $wysiwyg = $this->getWysiwyg($field);
    $textarea_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//div[contains(@class, "ck-source-editing-area")]//textarea');
    if (empty($textarea_elements)) {
      throw new \Exception("Could not find the textarea for the '$field' field.");
    }
    if (count($textarea_elements) > 1) {
      throw new \Exception("Multiple textareas found for '$field'.");
    }
    $textarea = reset($textarea_elements);
    $textarea->setValue($text);
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
    if (empty($label_elements)) {
      throw new \Exception("Could not find the '$field' field label.");
    }
    if (count($label_elements) > 1) {
      throw new \Exception("Multiple '$field' labels found in the page.");
    }
    $wysiwyg_elements = $driver->find('//label[contains(text(), "' . $field . '")]/following::div[contains(@class, " ck-editor ")][1]');
    if (empty($wysiwyg_elements)) {
      throw new \Exception("Could not find the '$field' wysiwyg editor.");
    }
    if (count($wysiwyg_elements) > 1) {
      throw new \Exception("Multiple '$field' wysiwyg editors found in the page.");
    }
    return reset($wysiwyg_elements);
  }

  /**
   * Enters the given text in the given WYSIWYG editor.
   *
   * If this is running on a JavaScript enabled browser it will first click the
   * 'Source' button so the text can be entered as normal HTML.
   *
   * @param string $label
   *   The label of the field containing the WYSIWYG editor.
   * @param string $text
   *   The text to enter in the WYSIWYG editor.
   */
  protected function enterTextInWysiwyg(string $label, string $text): void {
    // If we are running in a JavaScript enabled browser, first click the
    // 'Source' button so we can enter the text as HTML and get the same result
    // as in a non-JS browser.
    if ($this->browserSupportsJavaScript()) {
      $this->pressWysiwygButton($label, 'Source');
      $this->setWysiwygText($label, $text);
      // Make sure we switch back to normal view and let javascript to
      // execute filters on the text and validate the html.
      $this->pressWysiwygButton($label, 'Source');
    }
    else {
      $this->getSession()->getPage()->fillField($label, $text);
    }
  }

}
