<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Traits\WysiwygTrait;
use DrupalTest\BehatTraits\Traits\BrowserCapabilityDetectionTrait;
use PHPUnit\Framework\Assert;

/**
 * Behat feature context file which contains specific steps for wysiwyg fields.
 */
class WysiwygContext extends RawDrupalContext {

  use BrowserCapabilityDetectionTrait;
  use WysiwygTrait;

  /**
   * Checks that a given field label is associated with a WYSIWYG editor.
   *
   * @param string $label
   *   The label of the field containing the WYSIWYG editor.
   *
   * @Then I should see the :label wysiwyg editor
   */
  public function assertWysiwyg($label) {
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
  public function assertNoWysiwyg($label) {
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
  public function iShouldSeeWysiwygButton($field, $button) {
    $wysiwyg = $this->getWysiwyg($field);
    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//a[@title="' . $button . '"]');
    if (empty($button_elements)) {
      throw new \Exception("Could not find the '$button' button.");
    }
    if (count($button_elements) > 1) {
      throw new \Exception("Multiple '$button' buttons found in the editor.");
    }
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
  public function iShouldNotSeeWysiwygButton($field, $button) {
    $wysiwyg = $this->getWysiwyg($field);
    $button_elements = $this->getSession()->getDriver()->find($wysiwyg->getXpath() . '//a[@title="' . $button . '"]');
    if (!empty($button_elements)) {
      throw new \Exception("The '$button' button is present.");
    }
    if (count($button_elements) > 1) {
      throw new \Exception("Multiple '$button' buttons found in the editor.");
    }
  }

  /**
   * Enters the given text in the given WYSIWYG editor.
   *
   * If this is running on a JavaScript enabled browser it will first click the
   * 'Source' button so the text can be entered as normal HTML.
   *
   * @param string $text
   *   The text to enter in the WYSIWYG editor.
   * @param string $label
   *   The label of the field containing the WYSIWYG editor.
   *
   * @When I enter :text in the :label wysiwyg editor
   */
  public function enterTextInWysiwyg($text, $label) {
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

}
