<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_project\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of Project content through the UI.
 *
 * @group oe_content
 * @group batch3
 */
class ProjectContentTest extends ContentTestBase {

  /**
   * Tests the creation of a Project content through the UI.
   */
  public function testProjectCreation(): void {
    \Drupal::service('module_installer')->install([
      'oe_media_oembed_mock',
    ]);
    \Drupal::service('theme_installer')->install([
      'olivero',
    ]);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'olivero')->save();

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_project content',
      'edit any image media',
      'edit own oe_project content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createImageMedia('Image 1', 'example_1.jpeg', 'Alternative text 1');
    $this->createImageMedia('Image 2', 'example_1.jpeg', 'Alternative text 2');
    $this->createImageMedia('Image 3', 'example_1.jpeg', 'Alternative text 3');
    $this->createImageMedia('Contact image', 'example_1.jpeg', 'Alternative text 4');
    $this->createRemoteVideoMedia('https://www.youtube.com/watch?v=YaUGTOnf6k0');
    // Create the documents to be referenced later on.
    $this->createDocumentMedia('My Document 1', 'sample.pdf');
    $this->createDocumentMedia('My Document 2', 'document.pdf');

    $this->drupalGet('/node/add/oe_project');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // Fill in the mandatory fields.
    $page->fillField('Page title', 'My Project');
    $page->fillField('Subject tags', 'EU financing');
    $page->fillField('Body text', 'Body text');
    $this->fillFieldInRegion('Alternative titles and teaser', 'Teaser', 'Project teaser text');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');

    // Fill in the Stakeholder fields of the Coordinators field.
    $page->pressButton('Add new coordinator');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project coordinators', 'Name', 'Coordinators stakeholder');
    $this->fillFieldInRegion('Project coordinators', 'Acronym', 'Acronym of the Coordinator');
    $this->fillFieldInRegion('Project coordinators', 'Use existing media', 'Image 1');
    $this->selectFieldOptionInRegion('Project coordinators', 'Country', 'Belgium');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project coordinators', 'Street address', 'Rue belliard 28');
    $this->fillFieldInRegion('Project coordinators', 'Postal code', '1000');
    $this->fillFieldInRegion('Project coordinators', 'City', 'Brussels');
    $this->fillFieldInRegion('Project coordinators', 'Website', 'https://ec.europa.eu/website');
    $this->fillFieldInRegion('Project coordinators', 'Contact page URL', 'https://ec.europa.eu/contact');

    // Fill in the Stakeholder fields of the Participants field.
    $page->pressButton('Add new participant');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project participants', 'Name', 'Participants stakeholder');
    $this->fillFieldInRegion('Project participants', 'Acronym', 'Acronym of the Participant');
    $this->fillFieldInRegion('Project participants', 'Use existing media', 'Image 2');
    $this->selectFieldOptionInRegion('Project participants', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project participants', 'Website', 'https://ec.europa.eu/website');
    $this->fillFieldInRegion('Project participants', 'Contact page URL', 'https://ec.europa.eu/contact');

    // Fill in the optional fields.
    $page->fillField('Summary', 'Summary text');
    $page->fillField('Reference', 'Reference text');
    $this->fillDateRangeField('Start date', 'Project period', 'date', '23-02-2019');
    $this->fillDateRangeField('End date', 'Project period', 'date', '24-02-2019');
    $this->fillFieldInRegion('Budget', 'Overall budget', '1000');
    $this->fillFieldInRegion('Budget', 'EU contribution', '200');
    $page->fillField('Funding programme', 'Anti Fraud Information System (AFIS) (http://publications.europa.eu/resource/authority/eu-programme/AFIS2020)');
    $this->fillFieldInRegion('Project Website', 'URL', 'http://project.website');
    $this->fillFieldInRegion('Project Website', 'Link text', 'Website');
    $this->fillFieldInRegion('featured media form element', 'Media item', 'Image 3');
    $this->fillFieldInRegion('featured media form element', 'Caption', 'Here is my featured image text caption.');
    $this->fillFieldInRegion('Call for proposals', 'URL', 'http://example.com');
    $this->fillFieldInRegion('Call for proposals', 'Link text', 'Example proposal');
    $this->fillFieldInRegion('Project documents', 'Use existing media', 'My Document 2');
    $this->fillFieldInRegion('Result', 'Results', 'Result 1 text');
    $this->fillFieldInRegion('Result', 'Use existing media', 'My Document 1');
    $this->fillFieldInRegion('Alternative titles and teaser', 'Alternative title', 'My alternative title text');
    $this->fillFieldInRegion('Alternative titles and teaser', 'Navigation title', 'My navigation title text');
    $page->fillField('Departments', 'Audit Board of the European Communities');

    // Project contact field group.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project contact', 'Name', 'Name of the project contact');
    $this->fillFieldInRegion('Project contact', 'Organisation', 'Project contact organisation');
    $this->fillFieldInRegion('Project contact', 'Body text', 'Project contact body text');
    $this->fillFieldInRegion('Project contact', 'Website', 'http://www.example.com/project_contact');
    $this->fillFieldInRegion('Project contact', 'Email', 'project_contact@example.com');
    $this->fillFieldInRegion('Project contact', 'Phone number', '0488779033');
    $this->fillFieldInRegion('Project contact', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('Project contact', 'Fax number', '0488779035');
    $this->selectFieldOptionInRegion('Project contact', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project contact', 'Street address', 'Project contact street');
    $this->fillFieldInRegion('Project contact', 'Postal code', '9000');
    $this->fillFieldInRegion('Project contact', 'City', 'Budapest');
    $this->fillFieldInRegion('Project contact', 'Office', 'Project contact office');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:project_contact_social@example.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'Project contact social link email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('Project contact', 'Media item', 'Contact image');
    $this->fillFieldInRegion('Project contact', 'Caption', 'Project contact caption');
    $this->fillFieldInRegion('Project contact', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');

    // Fill in the Project locations field.
    $this->selectFieldOptionInRegion('Project locations', 'Country', 'Spain');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Project locations', 'Postal code', '09199');
    $this->fillFieldInRegion('Project locations', 'City', 'Ages');
    $this->selectFieldOptionInRegion('Project locations', 'Province', 'Burgos');

    $page->pressButton('Save');

    $assert_session->pageTextContains('Project My Project has been created.');
    $assert_session->pageTextContains('My Project');
    $assert_session->pageTextContains('EU financing');
    $assert_session->pageTextContains('Body text');

    // Project coordinators field values.
    $this->assertRegionText('Project coordinators', 'Coordinators stakeholder');
    $this->assertRegionText('Project coordinators', 'Acronym of the Coordinator');
    $this->assertRegionText('Project coordinators', 'Logo');
    $this->assertRegionText('Project coordinators', 'Belgium');
    $this->assertRegionText('Project coordinators', 'Rue belliard 28');
    $this->assertRegionText('Project coordinators', '1000');
    $this->assertRegionText('Project coordinators', 'Brussels');
    $this->assertRegionText('Project coordinators', 'https://ec.europa.eu/website');
    $this->assertRegionText('Project coordinators', 'https://ec.europa.eu/contact');

    // Project participants field values.
    $this->assertRegionText('Project participants', 'Participants stakeholder');
    $this->assertRegionText('Project participants', 'Acronym of the Participant');
    $this->assertRegionText('Project participants', 'Logo');
    $this->assertRegionText('Project participants', 'Hungary');
    $this->assertRegionText('Project participants', 'https://ec.europa.eu/website');
    $this->assertRegionText('Project participants', 'https://ec.europa.eu/contact');

    // Project field values.
    $assert_session->pageTextContains('Summary text');
    $this->assertRegionText('featured media field', 'Image 3');
    $this->assertRegionText('featured media field', 'Here is my featured image text caption.');
    $assert_session->pageTextContains('Reference text');
    $assert_session->pageTextContains('2019-02-23');
    $assert_session->pageTextContains('2019-02-24');
    $assert_session->pageTextContains('1000');
    $assert_session->pageTextContains('200');
    $assert_session->pageTextContains('Anti Fraud Information System (AFIS)');
    $assert_session->pageTextContains('Website');
    $assert_session->pageTextContains('Example proposal');
    $assert_session->pageTextContains('Result 1 text');
    $this->assertRegionText('Project documents', 'document.pdf');
    $this->assertRegionText('Project result files', 'sample.pdf');
    $assert_session->pageTextContains('Audit Board of the European Communities');

    // Project contact values.
    $assert_session->pageTextContains('Name of the project contact');
    $assert_session->pageTextContains('Project contact body text');
    $assert_session->pageTextContains('Project contact organisation');
    $assert_session->linkExists('http://www.example.com/project_contact');
    $assert_session->pageTextContains('project_contact@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->pageTextContains('Project contact street');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->linkExists('Project contact social link email');
    $assert_session->pageTextContains('Project contact office');
    $assert_session->linkExists('Contact image');
    $assert_session->pageTextContains('Project contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');

    // Project locations values.
    $assert_session->pageTextContains('Spain');
    $assert_session->pageTextContains('09199');
    $assert_session->pageTextContains('Ages');
    $assert_session->pageTextContains('Burgos');

    // A remote video can be used as featured media.
    $node = $this->getNodeByTitle('My Project');
    $this->drupalGet($node->toUrl('edit-form'));
    $this->fillFieldInRegion('featured media form element', 'Media item', 'Plant health in the EU');
    $this->fillFieldInRegion('featured media form element', 'Caption', 'Here is my featured video text caption.');
    $this->fillFieldInRegion('Budget', 'Overall budget', '104479592');
    $this->fillFieldInRegion('Budget', 'EU contribution', '104479592');
    $this->getSession()->getPage()->pressButton('Save');

    $this->assertRegionText('featured media field', 'Plant health in the EU');
    $assert_session->pageTextContains('104479592');
    $this->assertRegionText('featured media field', 'Here is my featured video text caption.');

    // Test the other budget limits.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->fillFieldInRegion('Budget', 'Overall budget', '99999.0000');
    $this->fillFieldInRegion('Budget', 'EU contribution', '20.13');
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('€99999.00');
    $assert_session->pageTextContains('€20.13');
  }

  /**
   * Tests that removing stakeholders and contacts only removes the references.
   */
  public function testStakeholderAndContactRemoval(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_project content',
      'edit any oe_project content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $coordinator_to_remove = $this->createStakeholderOrganisation(['name' => 'Coordinator to remove']);
    $participant_to_remove = $this->createStakeholderOrganisation(['name' => 'Participant to remove']);

    $node = $this->drupalCreateNode([
      'type' => 'oe_project',
      'title' => 'Project demo page',
      'oe_summary' => 'Project summary',
      'oe_project_website' => [
        'uri' => 'http://example.com',
        'title' => 'Project website',
      ],
      'oe_teaser' => 'Project teaser',
      'body' => 'Project body text',
      'oe_project_results' => 'Results text',
      'oe_project_coordinators' => [$coordinator_to_remove],
      'oe_project_participants' => [$participant_to_remove],
      'oe_project_contact' => $contact,
      'oe_project_locations' => [
        'country_code' => 'GB',
        'locality' => 'London',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);

    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session = $this->assertSession();

    $this->pressButtonInRegion('Project coordinators', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove Coordinator to remove?');
    $this->pressButtonInRegion('Project coordinators', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->pressButtonInRegion('Project participants', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove Participant to remove?');
    $this->pressButtonInRegion('Project participants', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->pressButtonInRegion('Project contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('Project contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Project Project demo page has been updated.');
    $this->assertEntityExists($coordinator_to_remove);
    $this->assertEntityExists($participant_to_remove);
    $this->assertEntityExists($contact);
  }

}
