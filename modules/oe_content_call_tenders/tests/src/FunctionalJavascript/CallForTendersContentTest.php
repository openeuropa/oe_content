<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_call_tenders\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation of Call for tenders content through the UI.
 *
 * @group oe_content
 * @group batch3
 */
class CallForTendersContentTest extends ContentTestBase {

  /**
   * Tests the creation of a Call for tenders content through the UI.
   */
  public function testCallForTendersCreation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_call_tenders content',
      'edit own oe_call_tenders content',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createDocumentMedia('My Document 1', 'sample.pdf');

    $this->drupalGet('/node/add/oe_call_tenders');
    $page = $this->getSession()->getPage();

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');

    $page->fillField('Page title', 'My Call for tenders 1');
    $page->fillField('Subject tags', 'EU financing');
    $page->fillField('Teaser', 'My Teaser text');
    $page->fillField('Introduction', 'My Introduction text');
    $page->fillField('Reference', 'My Reference text');
    $this->fillDateField('Publication date', 'date', '14-07-2020');
    $this->fillDateField('Opening of tenders', 'date', '24-07-2020');
    $this->fillDateField('Deadline date', 'date', '31-07-2020');
    $this->fillDateField('Deadline date', 'time', '23:45:00');
    $page->fillField('Responsible department', 'Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)');
    $page->fillField('Body text', 'My Body text');
    $this->fillFieldInRegion('Documents', 'Use existing media', 'My Document 1');
    $page->pressButton('Save');

    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('Call for tenders My Call for tenders 1 has been created.');
    $assert_session->pageTextContains('My Call for tenders 1');
    $assert_session->pageTextContains('My Teaser text');
    $assert_session->pageTextContains('My Introduction text');
    $assert_session->pageTextContains('My Reference text');
    $assert_session->pageTextContains('14 Jul 2020');
    $assert_session->pageTextContains('24 Jul 2020');
    $assert_session->pageTextContains('31 Jul 2020 - 23:45');
    $assert_session->pageTextContains('Audit Board of the European Communities');
    $assert_session->pageTextContains('My Body text');
    $assert_session->pageTextContains('My Document 1');
    $assert_session->pageTextNotContains('Committee on Agriculture and Rural Development');

    // Create another call for tenders to assert that the length limited fields
    // truncate the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_call_tenders');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Nam eleifend ipsum. Text to remove');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Subject tags', 'EU financing');
    $this->fillDateField('Publication date', 'date', '14-07-2020');
    $this->fillDateField('Deadline date', 'date', '31-07-2020');
    $this->fillDateField('Deadline date', 'time', '23:45:00');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)');
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

}
