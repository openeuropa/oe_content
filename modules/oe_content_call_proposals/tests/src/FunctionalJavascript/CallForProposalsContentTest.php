<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_call_proposals\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of Call for proposals content.
 *
 * @group oe_content
 * @group batch3
 */
class CallForProposalsContentTest extends ContentTestBase {

  /**
   * Tests the creation of a Call for proposals content through the UI.
   */
  public function testCallForProposalsCreation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_call_proposals content',
      'edit own image media',
      'edit own oe_call_proposals content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createDocumentMedia('My Document 1', 'sample.pdf');
    $this->createImageMedia('Contact image', 'example_1.jpeg', 'Contact image alternative text');

    $this->drupalGet('/node/add/oe_call_proposals');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->assertRegionText('Deadline model', 'Single-stage');
    $this->assertRegionText('Deadline model', 'Two-stage');
    $this->assertRegionText('Deadline model', 'Multiple cut-off');
    $this->assertRegionText('Deadline model', 'Permanent');

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('Publication in the official journal', 'Content limited to 128 characters, remaining: 128');
    $this->assertRegionText('Reference code form element', 'Content limited to 150 characters, remaining: 150');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');

    // Fill in the mandatory fields.
    $page->fillField('Page title', 'My Call for proposals 1');
    $this->fillDateField('Publication date', 'date', '24-10-2020');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)');
    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Subject tags', 'financing');

    // Fill in an invalid deadline date and switch to Permanent. No Deadline
    // date field is visible when the Permanent model is selected.
    $this->selectRadioButton('Single-stage');
    $this->fillDateField('Deadline date', 'date', '31-12-2020');
    $this->selectRadioButton('Permanent');
    $assert_session->pageTextNotContains('Deadline date');

    $page->pressButton('Save');
    $assert_session->pageTextContains('Call for proposals My Call for proposals 1 has been created.');

    $node = $this->getNodeByTitle('My Call for proposals 1');
    $this->drupalGet($node->toUrl('edit-form'));
    $this->selectRadioButton('Two-stage');
    $this->fillDateField('Deadline date', 'date', '31-12-2020');
    $this->fillDateField('Deadline date', 'time', '23:45:00');
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Call for proposals My Call for proposals 1 has been updated.');
    $assert_session->pageTextContains('My call for proposals 1');
    $assert_session->pageTextContains('24 Oct 2020');
    $assert_session->pageTextContains('Two-stage');
    $assert_session->pageTextContains('31 Dec 2020 - 23:45');
    $assert_session->pageTextContains('Teaser text');
    $assert_session->linkExists('financing');

    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->fillField('Body text', 'My Call for proposals 1 body');
    $page->fillField('Introduction', 'My Introduction text');
    $page->fillField('Reference', 'My Call for proposals 1 reference');
    $this->fillFieldInRegion('Publication in the official journal', 'URL', 'http://example.com/1');
    $this->fillFieldInRegion('Publication in the official journal', 'Link text', 'Official Journal publication 1');
    $this->fillDateField('Opening date', 'date', '25-10-2020');
    $page->fillField('Awarded grants', 'http://example.com/2');
    $page->fillField('Funding programme', 'Anti Fraud Information System (AFIS) (http://publications.europa.eu/resource/authority/eu-programme/AFIS2020)');
    $page->fillField('Responsible department', 'Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)');
    $this->fillFieldInRegion('Documents', 'Use existing media', 'My Document 1');

    // Call for proposals contact field group.
    $this->pressButtonInRegion('Call for proposals contact', 'Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Call for proposals contact', 'Name', 'Name of the call for proposals contact');
    $this->fillFieldInRegion('Call for proposals contact', 'Organisation', 'Call for proposals contact organisation');
    $this->fillFieldInRegion('Call for proposals contact', 'Body text', 'Call for proposals contact body text');
    $this->fillFieldInRegion('Call for proposals contact', 'Website', 'http://www.example.com/call_for_proposals_contact');
    $this->fillFieldInRegion('Call for proposals contact', 'Email', 'test@example.com');
    $this->fillFieldInRegion('Call for proposals contact', 'Phone number', '0488779033');
    $this->fillFieldInRegion('Call for proposals contact', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('Call for proposals contact', 'Fax number', '0488779035');
    $this->selectFieldOptionInRegion('Call for proposals contact', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Call for proposals contact', 'Street address', 'Back street 3');
    $this->fillFieldInRegion('Call for proposals contact', 'Postal code', '9000');
    $this->fillFieldInRegion('Call for proposals contact', 'City', 'Budapest');
    $this->fillFieldInRegion('Call for proposals contact', 'Office', 'Call for proposals contact office');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:example@email.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'Call for proposals contact social link email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('Call for proposals contact', 'Media item', 'Contact image');
    $this->fillFieldInRegion('Call for proposals contact', 'Caption', 'Call for proposals contact caption');
    $this->fillFieldInRegion('Call for proposals contact', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');
    $page->fillField('Alternative title', 'Alternative title 1');
    $page->fillField('Navigation title', 'Navi title 1');
    $page->fillField('Redirect link', 'http://example.com');

    $page->pressButton('Save');

    $assert_session->pageTextContains('My Call for proposals 1 body');
    $assert_session->pageTextContains('My Call for proposals 1 reference');
    $assert_session->linkExists('Official Journal publication 1');
    $assert_session->pageTextContains('25 Oct 2020');
    $assert_session->linkExists('http://example.com/2');
    $assert_session->pageTextContains('Anti Fraud Information System (AFIS)');
    $assert_session->pageTextContains('Audit Board of the European Communities');
    $assert_session->pageTextContains('My Document 1');
    $assert_session->pageTextContains('Name of the call for proposals contact');
    $assert_session->pageTextContains('Call for proposals contact body text');
    $assert_session->pageTextContains('Call for proposals contact organisation');
    $assert_session->linkExists('http://www.example.com/call_for_proposals_contact');
    $assert_session->pageTextContains('test@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->pageTextContains('Back street 3');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->linkExists('Call for proposals contact social link email');
    $assert_session->pageTextContains('Call for proposals contact office');
    $assert_session->linkExists('Contact image');
    $assert_session->pageTextContains('Call for proposals contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');
    $assert_session->pageTextContains('Alternative title 1');
    $assert_session->pageTextNotContains('Navi title 1');

    // Create another call for proposals to assert the date requirement of the
    // "Two-stage" model and that the length limited fields truncate the
    // characters exceeding their limit.
    $this->drupalGet('/node/add/oe_call_proposals');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'My long Call for proposals');
    $this->fillDateField('Publication date', 'date', '24-10-2020');
    $this->selectRadioButton('Two-stage');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)');
    $this->fillFieldInRegion('Publication in the official journal', 'URL', 'http://example.com/1');
    $this->fillFieldInRegion('Publication in the official journal', 'Link text', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempora Link text. Text to remove');
    $page->fillField('Reference', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hedrerit a magna Reference. Text to remove');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Subject tags', 'financing');
    $page->pressButton('Save');

    // The "Two-stage" model does not accept an empty deadline date.
    $assert_session->pageTextContains('The selected "Two-stage" model requires a valid date!');
    $this->fillDateField('Deadline date', 'date', '31-12-2020');
    $this->fillDateField('Deadline date', 'time', '23:45:00');
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Call for proposals My long Call for proposals has been created.');
    $assert_session->pageTextNotContains('Text to remove');
    $assert_session->pageTextContains('consequat tempora Link text');
    $assert_session->pageTextContains('hedrerit a magna Reference');
    $this->assertCommonFieldsAreTruncated();
  }

  /**
   * Tests multiple Deadline Date values for the "Two-stage" model.
   */
  public function testMultipleDeadlineDates(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_call_proposals content',
      'edit own oe_call_proposals content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_call_proposals');
    $page = $this->getSession()->getPage();

    $page->fillField('Page title', 'My Call for proposals 1');
    $this->fillDateField('Publication date', 'date', '24-10-2020');
    $this->selectRadioButton('Two-stage');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)');
    $this->fillDateField('Deadline date', 'date', '31-12-2020');
    $this->fillDateField('Deadline date', 'time', '23:45:00');
    $this->pressButtonInRegion('Deadline date', 'Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillNthDateField('Deadline date', 'date', '15-01-2021', 2);
    $this->fillNthDateField('Deadline date', 'time', '12:00:00', 2);
    $this->fillFieldInRegion('Publication in the official journal', 'URL', 'http://example.com/1');
    $this->fillFieldInRegion('Publication in the official journal', 'Link text', 'Official Journal publication 1');
    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Subject tags', 'financing');

    $page->pressButton('Save');

    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('Call for proposals My Call for proposals 1 has been created.');
    $assert_session->pageTextContains('Thu, 31 Dec 2020 - 23:45');
    $assert_session->pageTextContains('Fri, 15 Jan 2021 - 12:00');
  }

  /**
   * Tests that removing a contact only removes the reference to it.
   */
  public function testCfpContactRemoval(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_call_proposals content',
      'edit any oe_call_proposals content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $node = $this->drupalCreateNode([
      'type' => 'oe_call_proposals',
      'title' => 'Proposals demo page',
      'oe_summary' => 'Call for proposals introduction text',
      'body' => 'Call for proposals body text',
      'oe_call_proposals_opening_date' => '2019-02-22',
      'oe_call_proposals_model' => 'single_stage',
      'oe_call_proposals_deadline' => '2019-03-21T18:30:00',
      'oe_call_proposals_grants' => 'http://example.com',
      'oe_call_proposals_funding' => $this->getReferenceTargetIds('node', 'oe_call_proposals', 'oe_call_proposals_funding', 'Connecting Europe Facility (CEF) (2014/2020)'),
      'oe_call_proposals_journal' => [
        'uri' => 'http://example.com',
        'title' => 'Publication link',
      ],
      'oe_call_proposals_contact' => $contact,
      'oe_publication_date' => '2019-02-21',
      'oe_reference_code' => 'CALL/100/10',
      'oe_departments' => $this->getReferenceTargetIds('node', 'oe_call_proposals', 'oe_departments', 'Directorate-General for Budget'),
      'oe_subject' => $this->getReferenceTargetIds('node', 'oe_call_proposals', 'oe_subject', 'export financing'),
      'oe_teaser' => 'Teaser',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ACER',
    ]);

    $this->drupalGet($node->toUrl('edit-form'));
    $this->selectRadioButton('Single-stage');
    $this->pressButtonInRegion('Call for proposals contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('Call for proposals contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $this->assertSession()->pageTextContains('Call for proposals Proposals demo page has been updated.');
    $this->assertEntityExists($contact);
  }

  /**
   * Selects a radio button by its label.
   *
   * @param string $label
   *   The label of the radio button.
   */
  protected function selectRadioButton(string $label): void {
    $radio = $this->getSession()->getPage()->find('named', ['radio', $label]);
    $this->assertNotNull($radio, sprintf('The radio button "%s" was not found.', $label));
    $radio->click();
  }

}
