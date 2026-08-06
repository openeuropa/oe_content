<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_persistent\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;
use Drupal\Tests\oe_content\Traits\WysiwygTrait;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the persistent URLs inserted through the WYSIWYG editor.
 *
 * @group oe_content
 * @group batch3
 */
class PersistentUrlWysiwygTest extends ContentTestBase {

  use WysiwygTrait;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    \Drupal::service('module_installer')->install([
      'ckeditor5',
      'content_translation',
      'language',
      'oe_content_persistent',
      'oe_content_persistent_test',
    ]);

    ConfigurableLanguage::createFromLangcode('fr')->save();
    \Drupal::configFactory()->getEditable('language.types')
      ->set('negotiation.language_interface.enabled', ['language-url' => 0])
      ->save();
  }

  /**
   * Tests that the persistent URLs work with the linkit module.
   */
  public function testPersistentUrlWithLinkit(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create av_portal_photo media',
      'create oe_news content',
      'create url aliases',
      'edit own oe_page content',
      'use text format base_html',
      'view published skos concept entities',
      'view the administration theme',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_news');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $page->fillField('Page title', 'News 1');
    $page->selectFieldOption('News type', 'Factsheet');
    $this->enterTextInWysiwyg('Teaser', 'Teaser text');
    $this->enterTextInWysiwyg('Body text', 'Body text');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->pressButton('Save');
    $assert_session->pageTextContains('News 1');

    // Create a second node linking to the first one.
    $this->drupalGet('/node/add/oe_news');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'News 2');
    $this->insertWysiwygLink('Introduction', 'News 1');
    $this->enterTextInWysiwyg('Teaser', 'Teaser text');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->find('named', ['link_or_button', 'URL alias'])->click();
    $page->fillField('URL alias', '/news-2');
    $page->selectFieldOption('News type', 'Factsheet');
    $page->pressButton('Save');
    $assert_session->pageTextContains('News 2');

    // The link to the first node is processed.
    $news_1 = $this->drupalGetNodeByTitle('News 1');
    $this->assertProcessedLink($news_1->label(), $news_1->toUrl()->toString());

    $this->drupalLogout();

    // Update the alias of the first node and assert the link is updated.
    $news_1->get('path')->alias = '/alias1';
    $news_1->save();
    $this->drupalGet('/news-2');
    $this->assertProcessedLink('News 1', '/build/alias1');
  }

  /**
   * Asserts that the persistent link of a node points to the given alias.
   *
   * @param string $node_title
   *   The title of the linked node.
   * @param string $alias
   *   The URL the link is expected to point to.
   */
  protected function assertProcessedLink(string $node_title, string $alias): void {
    $node = $this->drupalGetNodeByTitle($node_title);
    $persistent_url = $this->config('oe_content_persistent.settings')->get('base_url') . $node->uuid();

    $link = $this->getSession()->getPage()->findLink($persistent_url) ?: $this->getSession()->getPage()->findLink($node_title);
    $this->assertNotNull($link, sprintf('No link to the node "%s" was found.', $node_title));
    $this->assertEquals($alias, $link->getAttribute('href'));
  }

}
