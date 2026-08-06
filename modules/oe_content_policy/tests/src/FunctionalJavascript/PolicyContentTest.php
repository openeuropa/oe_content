<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_policy\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;
use Drupal\Tests\oe_content\Traits\CollapsibleFieldTrait;

/**
 * Tests the creation of Policy content through the UI.
 *
 * @group oe_content
 * @group batch1
 */
class PolicyContentTest extends ContentTestBase {

  use CollapsibleFieldTrait;

  /**
   * Tests the creation of a Policy content through the UI.
   */
  public function testPolicyCreation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_policy content',
      'edit own oe_policy content',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_policy');
    $page = $this->getSession()->getPage();

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');

    $page->fillField('Page title', 'My Policy item');
    $page->fillField('Introduction', 'Summary text');
    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Body text', 'Body text');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Responsible department', 'European Patent Office');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Redirect link', 'http://example.com');
    $page->fillField('Navigation title', 'Navi title');
    $page->fillField('Alternative title', 'Shorter title');

    // Fill in three items of the timeline field.
    $timeline = $page->find('css', '.field--name-oe-timeline');
    $this->fillCollapsibleRow($timeline, 1, [
      'Label' => 'Label 1',
      'Title' => 'Title 1',
      'Content' => 'Body 1',
    ]);
    $timeline->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillCollapsibleRow($timeline, 2, [
      'Label' => 'Label 2',
      'Title' => 'Title 2',
      'Content' => 'Body 2',
    ]);
    $timeline->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillCollapsibleRow($timeline, 3, [
      'Label' => 'Label 3',
      'Title' => 'Title 3',
      'Content' => 'Body 3',
    ]);

    $page->pressButton('Save');

    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('Policy My Policy item has been created.');
    $assert_session->pageTextContains('My Policy item');
    $assert_session->pageTextContains('Body text');
    $assert_session->pageTextContains('Label 1');
    $assert_session->pageTextContains('Title 1');
    $assert_session->pageTextContains('Body 1');
    $assert_session->pageTextContains('Label 2');
    $assert_session->pageTextContains('Title 2');
    $assert_session->pageTextContains('Body 2');
    $assert_session->pageTextContains('Label 3');
    $assert_session->pageTextContains('Title 3');
    $assert_session->pageTextContains('Body 3');
    $assert_session->pageTextContains('Summary text');
    $assert_session->pageTextContains('Teaser text');
    $assert_session->pageTextContains('Shorter title');
    $assert_session->buttonExists('Show full timeline');
    $assert_session->pageTextNotContains('Navi title');
    $assert_session->linkNotExists('financing');
    $assert_session->linkNotExists('European Patent Office');

    // Create another policy to assert that the length limited fields truncate
    // the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_policy');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'My long Policy');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Body text', 'Body text');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Responsible department', 'European Patent Office');
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

}
