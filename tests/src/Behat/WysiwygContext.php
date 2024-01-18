<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Mink\Exception\DriverException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\WysiwygTrait;
use PHPUnit\Framework\Assert;

/**
 * Behat feature context file which contains specific steps for wysiwyg fields.
 */
class WysiwygContext extends RawDrupalContext {

  use WysiwygTrait;

  /**
   * Checks that a given field label is associated with a WYSIWYG editor.
   *
   * @param string $label
   *   The label of the field containing the WYSIWYG editor.
   *
   * @Then I should see the :label wysiwyg editor
   */
  public function assertWysiwyg(string $label): void {
    Assert::assertTrue($this->hasWysiwyg($label));
  }

  /**
   * Checks that a given field label is not associated with a WYSIWYG editor.
   *
   * @param string $label
   *   The label of the field uncontaining the WYSIWYG editor.
   *
   * @Then the :label field should not have a wysiwyg editor
   */
  public function assertNoWysiwyg(string $label): void {
    Assert::assertFalse($this->hasWysiwyg($label));
  }

  /**
   * Checks the given WYSIWYG button is present in the specified field.
   *
   * @param string $field
   *   The field label of the field to which the WYSIWYG editor is attached. For
   *   example 'Body'.
   * @param string $button
   *   The title of the button to find.
   *
   * @Then I should see the wysiwyg button :button in the field :field
   */
  public function iShouldSeeWysiwygButton(string $field, string $button): void {
    $wysiwyg = $this->getWysiwyg($field);
    // Try to see if there is a dropdown button to reveal the button.
    $dropdown_button = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="Show more items"]');
    if (!empty($dropdown_button)) {
      $dropdown_button = reset($dropdown_button);
      $dropdown_button->click();
    }
    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="' . $button . '"][1]');
    Assert::assertNotEmpty($button_elements, "The '$button' button is not present.");
  }

  /**
   * Checks the given WYSIWYG button is not present in the specified field.
   *
   * @param string $field
   *   The field label of the field to which the WYSIWYG editor is attached. For
   *   example 'Body'.
   * @param string $button
   *   The title of the button to find.
   *
   * @Then I should not see the wysiwyg button :button in the field :field
   */
  public function iShouldNotSeeWysiwygButton(string $field, string $button): void {
    $wysiwyg = $this->getWysiwyg($field);
    // Try to see if there is a dropdown button to reveal the button.
    $dropdown_button = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="Show more items"]');
    if (!empty($dropdown_button)) {
      $dropdown_button = reset($dropdown_button);
      $dropdown_button->click();
    }
    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//button[@data-cke-tooltip-text="' . $button . '"][1]');
    Assert::assertEmpty($button_elements, "The '$button' button is present.");
  }

  /**
   * Enters the given text in the given CKEditor.
   *
   * @param string $text
   *   The text to enter in the CKEditor.
   * @param string $label
   *   The label of the field containing the CKEditor.
   *
   * @When I enter :text in the :label wysiwyg editor
   */
  public function enterTextInWysiwyg(string $text, string $label): void {
    if ($this->browserSupportsJavaScript()) {
      $this->setWysiwygText($label, $text);
    }
    else {
      $this->getSession()->getPage()->fillField($label, $text);
    }
  }

  /**
   * Insert link to node through wysiwyg and DrupalLink button.
   *
   * @param string $node_title
   *   Title of the node.
   * @param string $field
   *   Field title of the node.
   *
   * @When I insert a link to :node_title in the :field field through the WYSIWYG editor
   */
  public function iInsertDrupalLink(string $node_title, string $field) {
    $session = $this->getSession();
    $page = $session->getPage();

    $this->pressWysiwygButton($field, 'Link (Ctrl+K)');

    $this->waitForAjaxToFinish();

    $href_field = $page->findField('attributes[href]');
    // Trigger a keydown event to active a autocomplete search.
    $href_field->setValue($node_title);
    $href_field->keyDown(' ');

    $this->getSession()->wait(5000, "jQuery('.linkit-result-line.ui-menu-item').length > 0");

    // Find the first result and click it.
    $page->find('xpath', '//li[contains(@class, "linkit-result-line") and contains(@class, "ui-menu-item")][1]')->click();

    $page->find('css', '.editor-link-dialog button:contains("Save")')->click();

    $this->waitForAjaxToFinish();
  }

  /**
   * Wait for AJAX to finish.
   *
   * @see \Drupal\FunctionalJavascriptTests\JSWebAssert::assertWaitOnAjaxRequest()
   */
  public function waitForAjaxToFinish($event = NULL) {
    $condition = <<<JS
    (function() {
      function isAjaxing(instance) {
        return instance && instance.ajaxing === true;
      }
      var d7_not_ajaxing = true;
      if (typeof Drupal !== 'undefined' && typeof Drupal.ajax !== 'undefined' && typeof Drupal.ajax.instances === 'undefined') {
        for(var i in Drupal.ajax) { if (isAjaxing(Drupal.ajax[i])) { d7_not_ajaxing = false; } }
      }
      var d8_not_ajaxing = (typeof Drupal === 'undefined' || typeof Drupal.ajax === 'undefined' || typeof Drupal.ajax.instances === 'undefined' || !Drupal.ajax.instances.some(isAjaxing))
      return (
        // Assert no AJAX request is running (via jQuery or Drupal) and no
        // animation is running.
        (typeof jQuery === 'undefined' || (jQuery.active === 0 && jQuery(':animated').length === 0)) &&
        d7_not_ajaxing && d8_not_ajaxing
      );
    }());
JS;
    $result = $this->getSession()->wait(1000 * $this->getMinkParameter('ajax_timeout'), $condition);
    if (!$result) {
      if ($event) {
        /** @var \Behat\Behat\Hook\Scope\BeforeStepScope $event */
        $event_data = ' ' . json_encode([
          'name' => $event->getName(),
          'feature' => $event->getFeature()->getTitle(),
          'step' => $event->getStep()->getText(),
          'suite' => $event->getSuite()->getName(),
        ]);
      }
      else {
        $event_data = '';
      }
      throw new \RuntimeException('Unable to complete AJAX request.' . $event_data);
    }
  }

  /**
   * Checks whether the browser supports JavaScript.
   *
   * @see https://github.com/drupaltest/behat-traits/blob/8.x-1.x/src/Traits/BrowserCapabilityDetectionTrait.php
   *
   * @return bool
   *   Returns TRUE when the browser environment supports executing JavaScript
   *   code, for example because the test is running in Selenium or PhantomJS.
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

}
