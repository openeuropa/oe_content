<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_page\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of Page content through the UI.
 *
 * @group oe_content
 * @group batch2
 */
class PageContentTest extends ContentTestBase {

  /**
   * Tests the creation of a Page content through the UI.
   */
  public function testPageCreation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_page content',
      'edit own oe_page content',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_page');
    $page = $this->getSession()->getPage();

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');

    $page->fillField('Page title', 'My page');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Redirect link', 'http://example.com');
    $page->fillField('Navigation title', 'Navi title');
    $page->fillField('Alternative title', 'Shorter title');
    $page->fillField('Introduction', 'Summary text');
    $page->fillField('Body text', 'Body text');
    $page->fillField('URL', 'http://example.com');
    $page->fillField('Link text', 'My link');
    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Author', 'European Patent Office');
    $page->pressButton('Save');

    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('Page My page has been created.');
    $assert_session->pageTextContains('My page');
    $assert_session->pageTextContains('Body text');
    $assert_session->pageTextContains('Teaser text');
    $assert_session->pageTextContains('Summary text');
    $assert_session->pageTextContains('Shorter title');
    $assert_session->linkNotExists('financing');
    $assert_session->linkNotExists('European Patent Office');
    $assert_session->linkExists('My link');

    // Create another page to assert that the length limited fields truncate
    // the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_page');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'My long page');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

}
