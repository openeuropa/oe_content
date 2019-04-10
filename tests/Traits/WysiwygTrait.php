<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Element\NodeElement;

/**
 * Helper methods for interacting with WYSIWYG editors.
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
  public function hasWysiwyg($field) {
    try {
      $this->getWysiwyg($field);
      return TRUE;
    }
    // Only catch the specific exception that is thrown when the WYSIWYG editor
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
   * @param string $button
   *   The title of the button to click.
   */
  public function pressWysiwygButton($field, $button): void {
    $wysiwyg = $this->getWysiwyg($field);
    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//a[@title="' . $button . '"]');
    if (empty($button_elements)) {
      throw new \Exception("Could not find the '$button' button.");
    }
    if (count($button_elements) > 1) {
      throw new \Exception("Multiple '$button' buttons found in the editor.");
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
  public function setWysiwygText($field, $text): void {
    $wysiwyg = $this->getWysiwyg($field);
    $textarea_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//textarea');
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
  public function getWysiwyg($field): NodeElement {
    $driver = $this->getSession()->getDriver();
    $label_elements = $driver->find('//label[text()="' . $field . '"]');
    if (empty($label_elements)) {
      throw new \Exception("Could not find the '$field' field label.");
    }
    if (count($label_elements) > 1) {
      throw new \Exception("Multiple '$field' labels found in the page.");
    }
    $wysiwyg_id = 'cke_' . $label_elements[0]->getAttribute('for');
    $wysiwyg_elements = $driver->find('//div[@id="' . $wysiwyg_id . '"]');
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
  protected function enterTextInWysiwyg($label, $text): void {
    // If we are running in a JavaScript enabled browser, first click the
    // 'Source' button so we can enter the text as HTML and get the same result
    // as in a non-JS browser.
    if ($this->browserSupportsJavaScript()) {
      $this->pressWysiwygButton($label, 'Source');
      $this->setWysiwygText($label, $text);
    }
    else {
      $this->getSession()->getPage()->fillField($label, $text);
    }
  }

}
