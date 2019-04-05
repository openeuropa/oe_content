<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\FunctionalJavascript;

use Drupal\Core\Url;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Base class for Persistent URL Wysiwyg integration FunctionalJavaScript tests.
 */
class PersistentUrlWysiwygTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'system',
    'node',
    'path',
    'ckeditor',
    'editor',
    'content_translation',
    'filter',
    'linkit',
    'oe_content_persistent',
    'oe_content_persistent_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->drupalCreateContentType(['type' => 'page']);

    $user = $this->drupalCreateUser([
      'access content',
      'edit own page content',
      'create page content',
      'use text format basic_html',
    ]);
    $this->drupalLogin($user);

  }

  /**
   * Tests adding purl link through wysiwyg and correct work of filter.
   */
  public function testPersistentUrlWysiwygIntegration(): void {

    $session = $this->getSession();
    $page = $session->getPage();
    $web_assert = $this->assertSession();

    $node1 = $this->drupalCreateNode();

    $node2 = $this->drupalCreateNode();
    $this->drupalGet('/node/' . $node2->id() . '/edit');

    $this->click('a.cke_button__drupallink');
    // Wait for the form to load.
    $web_assert->assertWaitOnAjaxRequest();

    $href_field = $page->findField('attributes[href]');
    $href_field->hasAttribute('data-autocomplete-path');
    // Trigger a keydown event to active a autocomplete search.
    $href_field->setValue($node1->label());
    $href_field->keyDown(' ');

    // Wait for the results to load.
    $this->getSession()->wait(5000, "jQuery('.linkit-result-line.ui-menu-item').length > 0");

    // Find the first result and click it.
    $page->find('xpath', '//li[contains(@class, "linkit-result-line") and contains(@class, "ui-menu-item")][1]')->click();

    // Make sure the linkit field field is populated with the node url.
    $purl_url = Url::fromRoute('oe_content_persistent.redirect', ['uuid' => $node1->uuid()])->setAbsolute()->toString();
    $this->assertEquals($purl_url, $href_field->getValue(), 'The href field is populated with the node persistent url.');

    $this->click('.editor-link-dialog button:contains("Save")');

    // Wait for the dialog to close.
    $web_assert->assertWaitOnAjaxRequest();

    $fields = [
      'data-entity-type' => $node1->getEntityTypeId(),
      'data-entity-uuid' => $node1->uuid(),
      'data-entity-substitution' => 'canonical',
      'href' => $purl_url,
    ];
    foreach ($fields as $attribute => $value) {
      $link_attribute = $this->getLinkAttributeFromEditor($attribute);
      $this->assertEquals($value, $link_attribute, 'The link contain an attribute by the name of "' . $attribute . '" with a value of "' . $value . '"');
    }

    $this->click('#edit-submit');

    $processed_link = $page->find('css', '.field--name-body a');
    $actual_url = $processed_link->getAttribute('href');
    $this->assertSame($node1->toUrl()->toString(), $actual_url);

    $test_node_url = $node2->toUrl()->setAbsolute()->toString();

    $node1->get('path')->alias = '/alias1';
    $node1->save();

    $this->drupalGet($test_node_url);
    $processed_link = $page->find('css', '.field--name-body a');
    $actual_url = $processed_link->getAttribute('href');
    $this->assertSame($node1->toUrl()->toString(), $actual_url);

    $node1->get('path')->alias = '/alias2';
    $node1->save();

    $this->drupalGet($test_node_url);
    $this->getSession()->wait(15000);
    $processed_link = $page->find('css', '.field--name-body a');
    $actual_url = $processed_link->getAttribute('href');
    $this->assertSame($node1->toUrl()->toString(), $actual_url);

  }

  /**
   * Gets an attribute of the first link in the ckeditor editor.
   *
   * @param string $attribute
   *   The attribute name.
   *
   * @return string|null
   *   The attribute, or null if the attribute is not found on the element.
   */
  private function getLinkAttributeFromEditor($attribute) {
    // We can't use $session->switchToIFrame() here, because the iframe does not
    // have a name.
    $javascript = <<<JS
        (function(){
          var iframes = document.getElementsByClassName('cke_wysiwyg_frame');
          if (iframes.length) {
            var doc = iframes[0].contentDocument || iframes[0].contentWindow.document;
            var link = doc.getElementsByTagName('a')[0];
            return link.getAttribute("$attribute");
          }
        })()
JS;
    return $this->getSession()->evaluateScript($javascript);
  }

}
