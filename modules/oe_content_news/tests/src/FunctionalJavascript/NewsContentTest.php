<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_news\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of News content through the UI.
 *
 * @group oe_content
 * @group batch1
 */
class NewsContentTest extends ContentTestBase {

  /**
   * Tests the creation of a News content through the UI.
   */
  public function testNewsCreation(): void {
    // We install these here because the modules have some configs that have
    // dependencies which are not yet installed if we add them to the $modules
    // array.
    \Drupal::service('module_installer')->install([
      'media_avportal_mock',
      'oe_media_avportal_test',
    ]);

    $user = $this->drupalCreateUser([
      'access content',
      'create av_portal_photo media',
      'create oe_news content',
      'edit any image media',
      'edit own oe_page content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createImageMedia('Contact image', 'example_1.jpeg', 'Contact image alternative text');

    // Create a "Media AV portal photo".
    $this->drupalGet('/media/add/av_portal_photo');
    $page = $this->getSession()->getPage();
    $page->fillField('Media AV Portal Photo', 'https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15');
    $page->pressButton('Save');

    // Create a "News" content.
    $this->drupalGet('/node/add/oe_news');
    $page = $this->getSession()->getPage();

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $page->fillField('Page title', 'My News item');
    // The news types are SKOS concepts, their order is not guaranteed.
    $this->assertSelectOptions('News type', [
      'Commissioners’ weekly activities',
      'Daily news',
      'Factsheet',
      'General publications',
      'Minutes',
      'News announcement',
      'News article',
      'News blog',
      'Newsletter',
      'Press release',
      'Provisional data',
      'Questions and answers',
      'Schedule',
      'Speech',
      'Statement',
      'Supplementary information',
    ], FALSE);
    $page->fillField('Introduction', 'Summary text');
    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Body text', 'Body text');
    $page->fillField('Location', 'Budapest');
    $page->fillField('Reference', 'Reference text');
    $this->fillDateField('Publication date', 'date', '21-02-2019');
    $this->fillDateField('Last update date', 'date', '29-07-2021');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Author', 'European Patent Office');
    $page->fillField('Related department', 'ACP–EU Joint Assembly');
    // Reference the media photo to the news item.
    $page->fillField('Use existing media', 'Euro with miniature figurines');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Redirect link', 'http://example.com');
    $page->fillField('Navigation title', 'Navi title');
    $page->fillField('Alternative title', 'Shorter title');
    $this->fillFieldInRegion('Related links', 'URL', 'http://example.com');
    $this->fillFieldInRegion('Related links', 'Link text', 'My link');
    $this->fillFieldInRegion('News sources', 'URL', 'https://www.example.com');
    $this->fillFieldInRegion('News sources', 'Link text', 'Source link text');

    // News contact field.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('News contact', 'Name', 'Name of the contact');
    $this->fillFieldInRegion('News contact', 'Organisation', 'News contact organisation');
    $this->fillFieldInRegion('News contact', 'Body text', 'News contact body text');
    $this->fillFieldInRegion('News contact', 'Website', 'http://www.example.com/news_contact');
    $this->fillFieldInRegion('News contact', 'Email', 'test@example.com');
    $this->fillFieldInRegion('News contact', 'Phone number', '0488779033');
    $this->fillFieldInRegion('News contact', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('News contact', 'Fax number', '0488779035');
    $this->selectFieldOptionInRegion('News contact', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('News contact', 'Street address', 'Back street 3');
    $this->fillFieldInRegion('News contact', 'Postal code', '9000');
    $this->fillFieldInRegion('News contact', 'City', 'Budapest');
    $this->fillFieldInRegion('News contact', 'Office', 'News contact office');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:example@email.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'News contact social link email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('News contact', 'Media item', 'Contact image');
    $this->fillFieldInRegion('News contact', 'Caption', 'News contact caption');
    $this->fillFieldInRegion('News contact', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');

    $page->pressButton('Save');

    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('News My News item has been created.');

    // News contact values.
    $assert_session->pageTextContains('Name of the contact');
    $assert_session->pageTextContains('News contact body text');
    $assert_session->pageTextContains('News contact organisation');
    $assert_session->linkExists('http://www.example.com/news_contact');
    $assert_session->pageTextContains('test@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->pageTextContains('Back street 3');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->linkExists('News contact social link email');
    $assert_session->pageTextContains('News contact office');
    $assert_session->linkExists('Contact image');
    $assert_session->pageTextContains('News contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');

    // Assert the rest of the values.
    $assert_session->pageTextContains('My News item');
    $assert_session->linkExists('Source link text');
    $assert_session->linkExists('My link');
    $assert_session->pageTextContains('Reference text');
    $assert_session->pageTextContains('Shorter title');
    $assert_session->pageTextContains('Teaser text');
    $assert_session->pageTextContains('Summary text');
    $assert_session->pageTextNotContains('Navi title');
    $assert_session->linkNotExists('Budapest');
    $assert_session->pageTextNotContains('Thu, 02/21/2019');
    $assert_session->pageTextNotContains('Thu, 07/29/2021');
    $assert_session->linkNotExists('financing');
    $assert_session->linkNotExists('European Patent Office');

    // Create another news to assert that the length limited fields truncate
    // the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_news');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'My long news');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Body text', 'Body text');
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Author', 'European Patent Office');
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

  /**
   * Tests that removing a contact only removes the reference to it.
   */
  public function testNewsContactRemoval(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_news content',
      'edit any oe_news content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $news = $this->drupalCreateNode([
      'type' => 'oe_news',
      'title' => 'Test news',
      'oe_news_types' => $this->getReferenceTargetIds('node', 'oe_news', 'oe_news_types', 'News article'),
      'body' => 'Some text',
      'oe_reference_code' => 'Some reference',
      'oe_news_contacts' => $contact,
      'oe_teaser' => 'Some teaser',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);

    $this->drupalGet($news->toUrl('edit-form'));
    $this->pressButtonInRegion('News contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('News contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $this->assertSession()->pageTextContains('News Test news has been updated.');
    $this->assertEntityExists($contact);
  }

}
