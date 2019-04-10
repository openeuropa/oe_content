<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\node\NodeInterface;
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
   * @When I insert link to :node_title through wysiwyg editor in :field field
   */
  public function iInsertDrupalLink(string $node_title, string $field) {

    $session = $this->getSession();
    $page = $session->getPage();

    $this->pressWysiwygButton($field, 'Link (Ctrl+K)');

    $node1 = $this->getNodeByTitle($node_title);

    $this->waitForAjaxToFinish();

    $href_field = $page->findField('attributes[href]');
    $href_field->hasAttribute('data-autocomplete-path');
    // Trigger a keydown event to active a autocomplete search.
    $href_field->setValue($node1->label());
    $href_field->keyDown(' ');

    $this->getSession()->wait(5000, "jQuery('.linkit-result-line.ui-menu-item').length > 0");

    // Find the first result and click it.
    $page->find('xpath', '//li[contains(@class, "linkit-result-line") and contains(@class, "ui-menu-item")][1]')->click();

    $page->find('css', '.editor-link-dialog button:contains("Save")')->click();

    $this->waitForAjaxToFinish();
  }

  /**
   * Check link to node.
   *
   * @param string $node_title
   *   Title of the node.
   * @param string|null $langcode
   *   Langcode if applicable.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   *
   * @Then I should see link to :node_title node
   */
  public function assertProcessedPurlLink(string $node_title, $langcode = FALSE) {
    $session = $this->getSession();
    $page = $session->getPage();

    $node1 = $this->getNodeByTitle($node_title);

    if (!empty($langcode)) {
      $node1 = $node1->getTranslation($langcode);
    }

    $lang_prefix = $langcode ? '/' . $langcode : '';

    $node1_url = '/' . $this->getDrupalParameter('drupal')['drupal_root'] . $lang_prefix . $node1->toUrl()->toString();

    $link = $page->findLink($node_title);
    $processed_url = $link->getAttribute('href');
    if ($node1_url !== $processed_url) {
      throw new \Exception("Unexpected url of link '$processed_url'. Should be '$node1_url'.");
    }
  }

  /**
   * Set alias of the node.
   *
   * @param string $node_title
   *   Title of the node.
   * @param string $alias
   *   Alias of the node.
   *
   * @When I update alias of :node_title node to :alias
   */
  public function updateNodeAlias(string $node_title, string $alias) {
    $node = $this->getNodeByTitle($node_title);
    $node->get('path')->alias = $alias;
    $node->save();
  }

  /**
   * Set alias of the node with language.
   *
   * @param string $node_title
   *   Title of the node.
   * @param string $alias
   *   Alias of the node.
   * @param string $language_name
   *   Language name.
   *
   * @When I update alias of :node_title node to :alias for :language_name
   */
  public function updateNodeAliasWithLanguage(string $node_title, string $alias, string $language_name) {
    $langcode = $this->getLangcodeByName($language_name);
    $node = $this->getNodeByTitle($node_title);

    $translation = $node->addTranslation($langcode, $node->toArray());
    $translation->get('path')->alias = $alias;
    $translation->save();
  }

  /**
   * Make sure that link is correct.
   *
   * @param string $node1_title
   *   Title of the referenced entity.
   * @param string $node2_title
   *   Title of node where should be link.
   * @param string $language_name
   *   Language name.
   *
   * @Then I should see updated link to :node1_title on :language_name version of :node2_title page
   */
  public function assertProcessedLinkOnNodeWithLanguage(string $node1_title, string $node2_title, string $language_name) {
    $langcode = $this->getLangcodeByName($language_name);

    $node2 = $this->getNodeByTitle($node2_title);
    $node2_translated = $node2->addTranslation($langcode, $node2->toArray());
    $node2_translated->save();

    $this->visitPath($langcode . '/node/' . $node2->id());
    $this->assertProcessedPurlLink($node1_title, $langcode);
  }

  /**
   * Make sure that link is correct.
   *
   * @param string $node1_title
   *   Title of the referenced entity.
   * @param string $node2_title
   *   Title of node where should be link.
   *
   * @Then I should see updated link to :node1_title on :node2_title page
   */
  public function assertProcessedLinkOnNode(string $node1_title, string $node2_title) {
    $node2 = $this->getNodeByTitle($node2_title);
    $this->visitPath('node/' . $node2->id());
    $this->assertProcessedPurlLink($node1_title);
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
   * Retrieves a node by its title.
   *
   * @param string $title
   *   The node title.
   *
   * @return \Drupal\node\NodeInterface
   *   The node entity.
   */
  protected function getNodeByTitle(string $title): NodeInterface {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadByProperties([
      'title' => $title,
    ]);

    if (!$nodes) {
      throw new \Exception("Could not find node with title '$title'.");
    }

    if (count($nodes) > 1) {
      throw new \Exception("Multiple nodes with title '$title' found.");
    }

    return reset($nodes);
  }

  /**
   * Retrieves a langcode by language name.
   *
   * @param string $language_name
   *   Name of language.
   *
   * @return string
   *   Langcode of language.
   */
  protected function getLangcodeByName(string $language_name): string {
    $languages = \Drupal::service('language_manager')->getStandardLanguageList();

    foreach ($languages as $langcode => $language) {
      if ($language[0] === $language_name) {
        return $langcode;
      }
    }

    throw new \Exception("Language name '$language_name' is not valid.");
  }

}
