<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Traits;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
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
    $ckeditor5_id = $this->getCkeditor5Id($field);
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
   * Inserts a link to a node through the WYSIWYG editor and the Linkit widget.
   *
   * @param string $field
   *   The field label of the field to which the WYSIWYG editor is attached.
   * @param string $node_title
   *   The title of the node to link to.
   */
  protected function insertWysiwygLink(string $field, string $node_title): void {
    $session = $this->getSession();
    $page = $session->getPage();

    $this->pressWysiwygButton($field, 'Link (Ctrl+K)');

    // Wait for the CKEditor link form balloon to appear and for the Linkit
    // module to initialize the autocomplete on the URL input. Both are pure
    // CKEditor/JS operations, not Drupal AJAX requests.
    $session->wait(5000, "document.querySelector('.ck-link-form input.form-linkit-autocomplete') !== null");

    // Use JavaScript to set the value on the Linkit autocomplete input and
    // trigger the search. Direct Mink interaction with CKEditor elements can
    // fail due to CKEditor re-rendering the form and creating stale element
    // references.
    $escaped_title = addslashes($node_title);
    $session->executeScript("
      var input = document.querySelector('.ck-link-form input.form-linkit-autocomplete');
      var nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
      nativeInputValueSetter.call(input, '$escaped_title');
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new KeyboardEvent('keydown', { key: ' ', bubbles: true }));
    ");

    $session->wait(5000, "jQuery('.linkit-result-line.ui-menu-item').length > 0");

    // Find the first autocomplete result and click it.
    $result = $page->find('xpath', '//li[contains(@class, "linkit-result-line") and contains(@class, "ui-menu-item")][1]');
    Assert::assertNotNull($result, 'No linkit autocomplete results found.');
    $result->click();

    // Click the submit button.
    $submit = $page->find('css', '.ck-link-form button[type="submit"]');
    Assert::assertNotNull($submit, 'The link form submit button was not found.');
    $submit->click();

    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Checks whether the browser supports JavaScript.
   *
   * @return bool
   *   TRUE when the browser environment supports executing JavaScript code.
   */
  protected function browserSupportsJavaScript(): bool {
    $driver = $this->getSession()->getDriver();
    try {
      if (!$driver->isStarted()) {
        $driver->start();
      }
    }
    catch (DriverException $e) {
      throw new \RuntimeException('Could not start webdriver.', 0, $e);
    }

    try {
      $driver->executeScript('return;');
      return TRUE;
    }
    catch (UnsupportedDriverActionException $e) {
      return FALSE;
    }
    catch (DriverException $e) {
      throw new \RuntimeException('Could not execute JavaScript.', 0, $e);
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
   * @codingStandardsIgnoreStart
   */
  protected function getCkeditor5Id(string $label): string|int {
    $wysiwyg = $this->getWysiwyg($label);
    $textarea = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '/preceding-sibling::textarea');
    Assert::assertNotEmpty($textarea, "Could not find the '$label' textarea element.");

    $textarea = reset($textarea);
    $ckeditor_id = $textarea->getAttribute('data-ckeditor5-id');
    Assert::assertNotEmpty($ckeditor_id, "Could not find the '$label' textarea element's ckeditor5 id.");

    return $ckeditor_id;
  }

}
