<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_organisation\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of Organisation content through the UI.
 *
 * @group oe_content
 * @group batch2
 */
class OrganisationContentTest extends ContentTestBase {

  /**
   * Tests the creation of an Organisation content through the UI.
   */
  public function testOrganisationCreation(): void {
    // We install these here because the modules have some configs that have
    // dependencies which are not yet installed if we add them to the $modules
    // array.
    \Drupal::service('module_installer')->install([
      'media_avportal_mock',
      'oe_media_avportal_test',
      'oe_content_organisation_person_reference',
    ]);

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_organisation content',
      'edit any image media',
      'edit own oe_organisation content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createImageMedia('Image 1', 'placeholder.png', 'Alternative text 1');
    $this->createImageMedia('Contact image', 'example_1.jpeg', 'Contact image alternative text');
    $this->createAvPortalPhotoMedia('https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15');
    $this->drupalCreateNode([
      'type' => 'oe_person',
      'oe_person_first_name' => 'Jane',
      'oe_person_last_name' => 'Doe',
      'oe_person_gender' => $this->getReferenceTargetIds('node', 'oe_person', 'oe_person_gender', 'female'),
      'oe_subject' => 'http://data.europa.eu/uxp/1010',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ACER',
    ]);
    $this->createDocumentMedia('My Document 1', 'sample.pdf');

    $this->drupalGet('/node/add/oe_organisation');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $page->fillField('Page title', 'My Organisation');
    $page->fillField('Introduction', 'Organisation introduction');
    $page->fillField('Body text', 'Body text');
    $page->fillField('Use existing media', 'Image 1');
    $page->fillField('Acronym', 'Organisation Acronym');
    $page->fillField('Teaser', 'Organisation teaser text');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Subject tags', 'financing');
    $assert_session->pageTextNotContains('Non-EU organisation type');
    $page->selectFieldOption('Organisation type', 'Non-EU organisation');
    $assert_session->pageTextContains('Non-EU organisation type');

    $page->selectFieldOption('Organisation type', 'EU organisation');
    $this->disableBrowserRequiredFieldValidation();
    $page->pressButton('Save');
    $assert_session->statusMessageContains('Please select an EU organisation.', 'error');

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Organisation type', 'Non-EU organisation');
    $this->disableBrowserRequiredFieldValidation();
    $page->pressButton('Save');
    $assert_session->statusMessageContains('Please select a non-EU organisation type.', 'error');

    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Organisation type', 'EU organisation');
    $page->fillField('EU organisation', 'Audit Board of the European Communities');

    // Organisation contact field group.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Organisation contact', 'Name', 'Name of the organisation contact 1');
    $this->fillFieldInRegion('Organisation contact', 'Organisation', 'Contact organisation');
    $this->fillFieldInRegion('Organisation contact', 'Body text', 'Contact body text');
    $this->fillFieldInRegion('Organisation contact', 'Website', 'http://www.example.com/website');
    $this->selectFieldOptionInRegion('Organisation contact', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Organisation contact', 'Street address', 'Back street 3');
    $this->fillFieldInRegion('Organisation contact', 'Postal code', '9000');
    $this->fillFieldInRegion('Organisation contact', 'City', 'Budapest');
    $this->fillFieldInRegion('Organisation contact', 'Office', 'Contact office');
    $this->fillFieldInRegion('Organisation contact', 'Email', 'test@example.com');
    $this->fillFieldInRegion('Organisation contact', 'Phone number', '0488779033');
    $this->fillFieldInRegion('Organisation contact', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('Organisation contact', 'Fax number', '0488779035');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:example@email.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'Email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('Organisation contact', 'Media item', 'Contact image');
    $this->fillFieldInRegion('Organisation contact', 'Caption', 'Contact caption');
    $this->fillFieldInRegion('Organisation contact', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');
    $page->pressButton('Create contact');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Add another contact.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Organisation contact', 'Name', 'Name of the organisation contact 2');

    $page->fillField('Term', 'Overview Term text');
    $page->fillField('Description', 'Overview Description text');
    $this->fillFieldInRegion('Organisation chart', 'Use existing media', 'My Document 1');
    $page->fillField('URL', 'http://example.com');
    $page->fillField('Link text', 'Staff search');
    $page->fillField('Transparency', 'Organisation transparency text');
    $this->fillFieldInRegion('Organisation Transparency links', 'URL', 'https://example.com/transparency-link');
    $this->fillFieldInRegion('Organisation Transparency links', 'Link text', 'Transparency link');
    $page->fillField('Plans and reports', 'Organisation plans and reports text');
    $page->selectFieldOption('Link type', 'Email');
    $page->fillField('Persons', 'Jane Doe');
    $this->disableBrowserRequiredFieldValidation();
    $page->pressButton('Save');

    $assert_session->pageTextContains('Organisation My organisation has been created.');
    $assert_session->pageTextContains('My Organisation');
    $assert_session->pageTextContains('Organisation introduction');
    $assert_session->pageTextContains('Body text');
    $assert_session->pageTextContains('Image 1');
    $assert_session->pageTextContains('Organisation Acronym');
    $assert_session->pageTextContains('Organisation teaser text');
    $assert_session->pageTextContains('financing');

    // Organisation contacts values.
    $assert_session->pageTextContains('Name of the organisation contact 1');
    $assert_session->pageTextContains('Back street 3');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->pageTextContains('Contact office');
    $assert_session->pageTextContains('test@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->linkExists('Email');
    $assert_session->linkExists('Contact image');
    $assert_session->pageTextContains('Contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');
    $assert_session->pageTextContains('Name of the organisation contact 2');

    // Organisation type for EU organisations.
    $assert_session->pageTextContains('Organisation type EU organisation');
    $assert_session->pageTextContains('EU organisation Audit Board of the European Communities');
    $assert_session->pageTextContains('EU organisation type European Union corporate body');

    // Overview field values.
    $assert_session->pageTextContains('Overview Term text');
    $assert_session->pageTextContains('Overview Description text');

    // Organisation chart value.
    $assert_session->pageTextContains('sample.pdf');

    // Referenced person.
    $assert_session->pageTextContains('Jane Doe');

    // Staff search link value.
    $assert_session->linkExists('Staff search');

    // Transparency values.
    $assert_session->pageTextContains('Organisation transparency text');
    $assert_session->linkExists('Transparency link');

    // Plans and reports value.
    $assert_session->pageTextContains('Organisation plans and reports text');

    // Organisation type for non-EU organisations.
    $node = $this->getNodeByTitle('My Organisation');
    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('Organisation type', 'Non-EU organisation');
    $page->selectFieldOption('Non-EU organisation type', 'non-governmental organisation');
    $this->disableBrowserRequiredFieldValidation();
    $page->pressButton('Save');

    $assert_session->pageTextContains('Organisation type non-EU organisation');
    $assert_session->pageTextContains('Non-EU organisation type non-governmental organisation');

    // The logo can be an AV portal photo.
    $this->drupalGet($node->toUrl('edit-form'));
    $page->fillField('Use existing media', 'Euro with miniature figurines');
    $this->disableBrowserRequiredFieldValidation();
    $page->pressButton('Save');

    $assert_session->pageTextContains('Organisation My organisation has been updated.');
    $assert_session->pageTextContains('Euro with miniature figurines');
  }

  /**
   * Tests that removing a contact only removes the reference to it.
   */
  public function testOrganisationContactRemoval(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_organisation content',
      'edit any oe_organisation content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $node = $this->drupalCreateNode([
      'type' => 'oe_organisation',
      'title' => 'Organisation demo page',
      'oe_summary' => 'Organisation introduction text',
      'oe_subject' => $this->getReferenceTargetIds('node', 'oe_organisation', 'oe_subject', 'financing'),
      'oe_organisation_acronym' => 'Organisation acronym',
      'body' => 'Organisation body text',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => $this->getReferenceTargetIds('node', 'oe_organisation', 'oe_organisation_eu_org', 'Directorate-General for Budget'),
      'oe_organisation_contact' => $contact,
      'oe_teaser' => 'Organisation teaser',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);

    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->pressButton('Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Are you sure you want to remove A general contact?');
    $page->pressButton('Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton('Save');

    $this->assertSession()->pageTextContains('Organisation Organisation demo page has been updated.');
    $this->assertEntityExists($contact);
  }

}
