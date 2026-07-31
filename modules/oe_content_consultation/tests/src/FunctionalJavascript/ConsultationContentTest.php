<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_consultation\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of Consultation content through the UI.
 *
 * @group oe_content
 * @group batch3
 */
class ConsultationContentTest extends ContentTestBase {

  /**
   * Tests the creation of a Consultation content through the UI.
   */
  public function testConsultationCreation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_consultation content',
      'edit own oe_consultation content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $document_one = $this->createDocumentMedia('My Document 1', 'sample.pdf');
    $document_two = $this->createDocumentMedia('My Document 2', 'document.pdf');
    $this->drupalCreateNode([
      'type' => 'oe_publication',
      'title' => 'Publication node',
    ]);

    $this->drupalGet('/node/add/oe_consultation');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');

    $page->fillField('Page title', 'Consultation title');
    $page->fillField('Introduction', 'Introduction text');
    $this->fillDateField('Opening', 'date', '14-01-2021');
    $this->fillDateField('Deadline', 'date', '31-01-2021');
    $this->fillDateField('Deadline', 'time', '00:00:00');
    $page->fillField('Departments', 'Associated African States and Madagascar');
    $page->fillField('Target audience', 'Target audience text');
    $page->fillField('Why we are consulting', 'Why we are consulting text');
    $page->fillField('Respond to the consultation', 'Respond to the consultation text');
    $page->fillField('Respond to the consultation (closed status text)', 'Respond to the consultation (closed status text) text');
    $page->fillField('URL', 'http://respond.com');
    $page->fillField('Link text', 'Respond to the questionnaire');
    $page->fillField('Consultation outcome', 'Consultation outcome text');
    $page->fillField('Use existing media', 'My Document 1');
    $page->fillField('Additional information', 'Additional information text');
    $page->fillField('Legal notice', 'Legal notice text');

    // Create General contact.
    $page->pressButton('Add new Contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->fillField('Name', 'General contact');
    $page->pressButton('Create Contact');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Create document reference to Document media.
    $page->pressButton('Add new document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Consultation documents', 'Use existing media', sprintf('My Document 2 (%s)', $document_two->id()));
    $page->pressButton('Create document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('My document 2');

    // Create document reference to Publication node.
    $this->selectSingleOptionInRegion('Consultation documents', 'Publication');
    $page->pressButton('Add new document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Consultation documents', 'Publication', 'Publication node');
    $page->pressButton('Create document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Publication node');

    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Content owner', 'Audit Board of the European Communities');
    $page->fillField('Subject tags', 'export financing');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Consultation Consultation title has been created.');
    $assert_session->pageTextContains('Consultation title');
    $assert_session->pageTextContains('Introduction text');
    $assert_session->pageTextContains('14 Jan 2021');
    $assert_session->pageTextContains('31 Jan 2021 - 00:00');
    $assert_session->pageTextContains('Associated African States and Madagascar');
    $assert_session->pageTextContains('Target audience text');
    $assert_session->pageTextContains('Why we are consulting text');
    $assert_session->pageTextContains('Respond to the consultation text');
    $assert_session->pageTextContains('Respond to the consultation (closed status text)');
    $this->assertLinkPointsTo('Respond to the questionnaire', 'http://respond.com');
    $assert_session->pageTextContains('Consultation outcome text');
    $assert_session->pageTextContains('Additional information text');
    $assert_session->pageTextContains('Legal notice text');
    $assert_session->pageTextContains('sample.pdf');
    $assert_session->pageTextContains('General contact');
    // The document of the document reference is shown.
    $assert_session->pageTextContains('document.pdf');
    // The publication of the document reference is shown.
    $assert_session->pageTextContains('Publication node');

    // Create another consultation to assert that the length limited fields
    // truncate the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_consultation');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'Consultation title scelerisque eros mi, eget tempus nibh finibus sed. Praesent id ex bibendum, luctus nisl ut, suscipit lectus. Nullam vitae neque mi. Aliquam eleifend d Text to remove.');
    $this->fillDateField('Opening', 'date', '14-07-2020');
    $this->fillDateField('Deadline', 'date', '31-01-2021');
    $this->fillDateField('Deadline', 'time', '00:00:00');
    $page->fillField('Target audience', 'Target audience text');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Content owner', 'Audit Board of the European Communities');
    $page->fillField('Subject tags', 'export financing');
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

  /**
   * Tests the visibility and the removal of document references and contacts.
   */
  public function testDocumentReferenceVisibilityAndRemoval(): void {
    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $document = $this->createDocumentMedia('My Document 3', 'sample.pdf');
    $document_reference = $this->createDocumentReference('oe_document', [
      'oe_document' => $document,
    ]);
    $node = $this->drupalCreateNode([
      'type' => 'oe_consultation',
      'title' => 'Consultation demo page',
      'oe_teaser' => 'Consultation teaser',
      'oe_consultation_contacts' => $contact,
      'oe_consultation_opening_date' => '2019-02-22',
      'oe_consultation_deadline' => '2019-03-21T18:30:00',
      'oe_consultation_target_audience' => 'Target audience text',
      'oe_consultation_documents' => $document_reference,
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);

    // An anonymous user sees the document of the published document reference.
    $this->drupalGet($node->toUrl());
    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('sample.pdf');

    // Unpublish the document reference, it is not shown anymore.
    $document_reference->set('status', 0)->save();
    $this->getSession()->reload();
    $assert_session->pageTextNotContains('sample.pdf');

    // A user allowed to see the unpublished sub-entities still sees it.
    $this->drupalLogin($this->drupalCreateUser(['view unpublished sub entities']));
    $this->drupalGet($node->toUrl());
    $assert_session->pageTextContains('sample.pdf');

    // Removing the contact and the document reference from the node only
    // removes the references to them.
    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'create oe_consultation content',
      'edit any oe_consultation content',
      'manage corporate content entities',
      'view published skos concept entities',
      'view unpublished sub entities',
    ]));
    $this->drupalGet($node->toUrl('edit-form'));
    $this->pressButtonInRegion('Consultation contacts', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('Consultation contacts', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->pressButtonInRegion('Consultation documents', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove My Document 3?');
    $this->pressButtonInRegion('Consultation documents', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Consultation Consultation demo page has been updated.');
    $this->assertEntityExists($contact);
    $this->assertEntityExists($document_reference);
  }

  /**
   * Asserts that a link points to the given URL.
   *
   * @param string $link
   *   The label of the link.
   * @param string $url
   *   The URL the link is expected to point to.
   */
  protected function assertLinkPointsTo(string $link, string $url): void {
    $element = $this->getSession()->getPage()->findLink($link);
    $this->assertNotNull($element, sprintf('The link "%s" was not found.', $link));
    $this->assertEquals($url, $element->getAttribute('href'));
  }

}
