<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_publication\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Functional tests for the Publication content type.
 *
 * @group oe_content
 * @group batch3
 */
class PublicationContentTest extends ContentTestBase {

  /**
   * Tests the Publication content type form.
   */
  public function testPublicationForm() {
    $admin = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($admin);
    $this->drupalGet('/node/add/oe_publication');

    // Assert collection related field visibilities when loading the form.
    $this->assertTrue($this->getSession()->getPage()->findField('oe_documents[0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->find('css', '#edit-oe-documents')->hasAttribute('required'));
    $this->assertFalse($this->getSession()->getPage()->findField('oe_publication_publications[0][target_id]')->isVisible());

    // Mark the publication as collection and assert the field visibilities.
    $this->getSession()->getPage()->selectFieldOption('Yes', '1');
    $this->assertFalse($this->getSession()->getPage()->findField('oe_documents[0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_publication_publications[0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->find('css', '#oe-publication-publications-values h4')->hasClass('form-required'));

    // Create a publication and test the field constraints.
    $collection = \Drupal::entityTypeManager()->getStorage('node')->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'oe_publication_collection' => 0,
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $collection->save();

    $this->drupalGet($collection->toUrl('edit-form'));

    // Disable the browser required field validation and assert files field.
    $this->getSession()->executeScript("typeof jQuery === 'undefined' || jQuery(':input[required]').prop('required', false);");
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Files field is required');
    $this->getSession()->getPage()->selectFieldOption('Yes', '1');

    // Disable the browser required field validation and assert publications
    // field.
    $this->getSession()->executeScript("typeof jQuery === 'undefined' || jQuery(':input[required]').prop('required', false);");
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContainsOnce('Publications field is required');
  }

  /**
   * Tests the creation of a Publication content through the UI.
   */
  public function testPublicationCreation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_publication content',
      'edit own image media',
      'edit own oe_publication content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createDocumentMedia('My Document 1', 'sample.pdf');
    $this->createImageMedia('Sample image', 'example_1.jpeg', 'example text');

    $this->drupalGet('/node/add/oe_publication');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $assert_session->pageTextContains('This is a publication collection');
    $assert_session->pageTextNotContains('Publications');
    $assert_session->pageTextContains('Files');
    $page->selectFieldOption('Yes', '1');
    $assert_session->pageTextContains('Publications');
    $assert_session->pageTextNotContains('Files');
    $page->selectFieldOption('No', '0');

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');

    $page->fillField('Page title', 'My Publication item');
    $page->fillField('Introduction', 'Summary text');
    $page->fillField('Teaser', 'Teaser text');
    $page->fillField('Subject tags', 'financing');
    $this->fillDateField('Publication date', 'date', '21-02-2019');
    $this->fillFieldInRegion('Documents', 'Use existing media', 'My Document 1');
    $page->fillField('Resource type', 'Acknowledgement receipt');
    $this->fillFieldInRegion('Publication thumbnail', 'Use existing media', 'Sample image');
    $page->fillField('Author', 'European Patent Office');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Redirect link', 'http://example.com');
    $page->fillField('Navigation title', 'Navi title');
    $page->fillField('Alternative title', 'Shorter title');
    $this->fillDateField('Last update date', 'date', '29-07-2021');
    $page->fillField('Body', 'Body text');
    $this->fillFieldInRegion('Reference codes', 'Identifier code', '123456789');
    $this->pressButtonInRegion('Reference codes', 'Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertVisuallyVisible($page->find('css', "input[name='oe_reference_codes[1][value]']"));
    $page->fillField('Related department', 'European Labour Authority');
    $page->fillField('Country', 'Hungary');

    // Publication contact field group.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Publication contact', 'Name', 'Name of the publication contact');
    $this->fillFieldInRegion('Publication contact', 'Organisation', 'Publication contact organisation');
    $this->fillFieldInRegion('Publication contact', 'Body text', 'Publication contact body text');
    $this->fillFieldInRegion('Publication contact', 'Website', 'http://www.example.com/publication_contact');
    $this->fillFieldInRegion('Publication contact', 'Email', 'test@example.com');
    $this->fillFieldInRegion('Publication contact', 'Phone number', '0488779033');
    $this->fillFieldInRegion('Publication contact', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('Publication contact', 'Fax number', '0488779035');
    $this->selectFieldOptionInRegion('Publication contact', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Publication contact', 'Street address', 'Back street 3');
    $this->fillFieldInRegion('Publication contact', 'Postal code', '9000');
    $this->fillFieldInRegion('Publication contact', 'City', 'Budapest');
    $this->fillFieldInRegion('Publication contact', 'Office', 'Publication contact office');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:example@email.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'Publication contact social link email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('Publication contact', 'Media item', 'Sample image');
    $this->fillFieldInRegion('Publication contact', 'Caption', 'Publication contact caption');
    $this->fillFieldInRegion('Publication contact', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');

    $page->pressButton('Save');

    $assert_session->pageTextContains('Publication My Publication item has been created.');
    $assert_session->pageTextContains('My Publication item');
    $assert_session->pageTextContains('sample.pdf');
    $assert_session->pageTextContains('Contact');
    $assert_session->pageTextContains('Body text');
    $assert_session->pageTextContains('123456789');
    $assert_session->pageTextContains('European Labour Authority');
    $assert_session->pageTextContains('Hungary');

    // Publication contact data display.
    $assert_session->pageTextContains('Name of the publication contact');
    $assert_session->pageTextContains('Publication contact body text');
    $assert_session->pageTextContains('Publication contact organisation');
    $assert_session->linkExists('http://www.example.com/publication_contact');
    $assert_session->pageTextContains('test@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->pageTextContains('Back street 3');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->linkExists('Publication contact social link email');
    $assert_session->pageTextContains('Publication contact office');
    $assert_session->linkExists('Sample image');
    $assert_session->pageTextContains('Publication contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');
    $assert_session->pageTextContains('Summary text');
    $assert_session->pageTextContains('Shorter title');
    $assert_session->pageTextContains('Teaser text');

    $assert_session->pageTextNotContains('Acknowledgement receipt');
    $assert_session->pageTextNotContains('Navi title');
    $assert_session->linkNotExists('financing');
    $assert_session->linkNotExists('European Patent Office');

    // Create another publication to assert that the length limited fields
    // truncate the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_publication');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'My long Publication');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Author', 'European Patent Office');
    $page->fillField('Resource type', 'Acknowledgement receipt');
    $this->fillFieldInRegion('Documents', 'Use existing media', 'My Document 1');
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

  /**
   * Tests that removing a contact only removes the reference to it.
   */
  public function testPublicationContactRemoval(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_publication content',
      'edit any oe_publication content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $document = $this->createDocumentMedia('Document 1', 'sample.pdf');
    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $publication = $this->drupalCreateNode([
      'type' => 'oe_publication',
      'title' => 'Publication demo page',
      'oe_summary' => 'Publication introduction text',
      'oe_reference_codes' => 'PUB/100/1',
      'oe_publication_type' => $this->getReferenceTargetIds('node', 'oe_publication', 'oe_publication_type', 'Agenda'),
      'oe_publication_date' => '2019-02-21',
      'oe_publication_last_updated' => '2019-02-22',
      'oe_documents' => $document,
      'body' => 'Publication body text',
      'oe_publication_contacts' => $contact,
      'oe_author' => $this->getReferenceTargetIds('node', 'oe_publication', 'oe_author', 'Directorate-General for Budget'),
      'oe_departments' => $this->getReferenceTargetIds('node', 'oe_publication', 'oe_departments', 'Directorate-General for Communication'),
      'oe_teaser' => 'Teaser',
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);

    $this->drupalGet($publication->toUrl('edit-form'));
    $this->pressButtonInRegion('Publication contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('Publication contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $this->assertSession()->pageTextContains('Publication Publication demo page has been updated.');
    $this->assertEntityExists($contact);
  }

}
