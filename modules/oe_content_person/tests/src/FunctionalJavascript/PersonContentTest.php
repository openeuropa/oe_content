<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_person\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;
use Drupal\Tests\oe_content\Traits\CollapsibleFieldTrait;

/**
 * Functional tests for the Person content type.
 *
 * @group oe_content
 * @group batch2
 */
class PersonContentTest extends ContentTestBase {

  use CollapsibleFieldTrait;

  /**
   * Tests the Person content type form.
   */
  public function testPersonForm() {
    $admin = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($admin);
    $this->drupalGet('/node/add/oe_person');

    // Assert default state of the form when loading it.
    $eu_visible_fields = [
      'oe_departments[0][target_id]',
      'oe_person_media[0][target_id]',
      'oe_social_media_links[0][uri]',
      'oe_person_transparency_intro[0][value]',
      'oe_person_transparency_links[0][uri]',
      'oe_person_biography_intro[0][value]',
      'oe_person_biography_timeline[0][label]',
      'oe_person_cv[0][target_id]',
      'oe_person_interests_intro[0][value]',
      'oe_person_interests_file[0][target_id]',
    ];
    $non_eu_visible_fields = [
      'oe_person_organisation[0][target_id]',
    ];
    foreach ($eu_visible_fields as $field) {
      $this->assertTrue($this->getSession()->getPage()->findField($field)->isVisible());
    }
    foreach ($non_eu_visible_fields as $field) {
      $this->assertFalse($this->getSession()->getPage()->findField($field)->isVisible());
    }

    // Add a job to render the job form and assert the visible fields.
    $this->getSession()->getPage()->pressButton('Add new person job');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Both role fields are visible for EU Person.
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_reference][0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_name][0][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_description][0][value]')->isVisible());

    // Assert the job role helptext.
    $this->assertSession()->pageTextContainsOnce('Please fill in one of the Role fields but not both at the same time.');

    // Fill in the job fields, they should not be saved after
    // we change the type to non-eu.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][0][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');

    // Change the person type to non-eu
    // and assert the available fields have changed.
    $this->getSession()->getPage()->selectFieldOption('oe_person_type', 'non_eu');
    foreach ($eu_visible_fields as $field) {
      $this->assertFalse($this->getSession()->getPage()->findField($field)->isVisible());
    }
    foreach ($non_eu_visible_fields as $field) {
      $this->assertTrue($this->getSession()->getPage()->findField($field)->isVisible());
    }

    // Assert the job field visibility have also been updated.
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_reference][0][target_id]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_description][0][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_name][0][value]')->isVisible());

    // Assert the job field required status have also been updated.
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_reference][0][target_id]')->hasAttribute('required'));
    $this->assertEquals('required', $this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_name][0][value]')->getAttribute('required'));

    // Fill in the required fields.
    $this->getSession()->getPage()->fillField('oe_subject[0][target_id]', 'international finance (http://data.europa.eu/uxp/1016)');
    $this->getSession()->getPage()->fillField('oe_person_first_name[0][value]', 'John');
    $this->getSession()->getPage()->fillField('oe_person_last_name[0][value]', 'Doe');
    $this->getSession()->getPage()->selectFieldOption('oe_person_gender', 'http://publications.europa.eu/resource/authority/human-sex/NST');
    $this->getSession()->getPage()->fillField('oe_teaser[0][value]', 'Teaser text');
    $this->getSession()->getPage()->fillField('oe_content_content_owner[0][target_id]', 'Arab Common Market (http://publications.europa.eu/resource/authority/corporate-body/ACM)');

    // Fill in the job role name and save the person.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][0][oe_role_name][0][value]', 'Custom role');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Person John Doe has been created.');

    // Open the edit form and assert the job was saved.
    $this->drupalGet('/node/1/edit');
    $job_region = $this->getSession()->getPage()->find('css', '#edit-oe-person-jobs-wrapper');
    $job_region->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', 'Custom Role');

    // Assert the current status of the job fields.
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_description][0][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->hasAttribute('required'));
    $this->assertEquals('required', $this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->getAttribute('required'));

    // Change the person type and assert the fields
    // for the existing job are updated.
    $this->getSession()->getPage()->selectFieldOption('oe_person_type', 'eu');
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_description][0][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->isVisible());

    // Assert the job does not have a reference role.
    $this->assertEmpty($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->getValue());
    // But it has the custom role.
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', 'Custom Role');

    // Save and assert the person is updated.
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Person John Doe has been updated.');

    // Edit and assert the values were kept.
    $this->drupalGet('/node/1/edit');
    $job_region = $this->getSession()->getPage()->find('css', '#edit-oe-person-jobs-wrapper');
    $job_region->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertEmpty($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->getValue());
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', 'Custom Role');

    // Save without a job role and assert validation.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', '');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('The job role of an EU person cannot be empty. Please edit the related job entry and fix its role accordingly.');

    // Update the job with a reference role and set it to be an acting role.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');
    $this->getSession()->getPage()->checkField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_acting][value]');
    // Add back the custom job role.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', 'Custom role');

    // Save and assert validation.
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains("An EU person's job cannot have two roles. Please edit the related job entry and fix its role accordingly.");

    // Empty the role reference and save the person and assert it was updated.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', '');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Person John Doe has been updated.');

    // Edit the node again and assert the job values where updated.
    $this->drupalGet('/node/1/edit');
    $job_region = $this->getSession()->getPage()->find('css', '#edit-oe-person-jobs-wrapper');
    $job_region->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', '');
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', 'Custom role');
    // Empty the custom role and use a role reference.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]', '');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Person John Doe has been updated.');

    // Edit the node again and assert the job values where updated.
    $this->drupalGet('/node/1/edit');
    $job_region = $this->getSession()->getPage()->find('css', '#edit-oe-person-jobs-wrapper');
    $job_region->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertEmpty($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->getValue());

    // Change the type of person to non-eu and assert
    // the old job type is no longer stored.
    $this->getSession()->getPage()->selectFieldOption('oe_person_type', 'non_eu');
    $this->assertEmpty($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->getValue());

    // Add a new job and assert the available fields
    // are the ones for a non-eu person.
    $job_region = $this->getSession()->getPage()->find('css', '#edit-oe-person-jobs-wrapper');
    $job_region->pressButton('Add new person job');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_reference][0][target_id]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_description][0][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_name][0][value]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_reference][0][target_id]')->hasAttribute('required'));
    $this->assertEquals('required', $this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_name][0][value]')->getAttribute('required'));

    // Change the person type to eu and assert the first job's role
    // is kept and the new job's fields are updated.
    $this->getSession()->getPage()->selectFieldOption('oe_person_type', 'eu');
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_reference][0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_description][0][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_name][0][value]')->isVisible());

    // Save and assert the validation.
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains("Please fill in one of the Role fields of the EU Person's job.");

    // Fill in both role field and save to assert the validation.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][1][oe_role_name][0][value]', 'Custom role 2');
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][1][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains("Please fill in only one of the Role fields of the Person's job.");

    // Leave only the job role reference filled in and assert the node is
    // updated.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][1][oe_role_name][0][value]', '');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Person John Doe has been updated.');
  }

  /**
   * Tests the creation of a Person content through the UI.
   */
  public function testPersonCreation(): void {
    \Drupal::service('theme_installer')->install([
      'olivero',
    ]);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'olivero')->save();

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_person content',
      'edit own image media',
      'edit own oe_person content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createImageMedia('Image 1', 'example_1.jpeg', 'Alternative text 1');
    $this->createImageMedia('Image 2', 'example_1.jpeg', 'Alternative text 2');
    $this->createImageMedia('Contact image', 'example_1.jpeg', 'Alternative text 4');
    $this->createDocumentMedia('My Document 1', 'sample.pdf');
    $this->createDocumentMedia('My Document 2', 'document.pdf');
    $this->createDocumentMedia('My Document 3', 'document2.pdf');

    $organisation_contact = $this->createGeneralContact([
      'name' => 'A general contact in Organisation',
    ]);
    $this->drupalCreateNode([
      'type' => 'oe_organisation',
      'title' => 'Organisation as a contact',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => $this->getReferenceTargetIds('node', 'oe_organisation', 'oe_organisation_eu_org', 'Directorate-General for Budget'),
      'oe_organisation_contact' => $organisation_contact,
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);
    $this->drupalCreateNode([
      'type' => 'oe_organisation',
      'title' => 'Organisation demo page',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_eu_org' => $this->getReferenceTargetIds('node', 'oe_organisation', 'oe_organisation_eu_org', 'Directorate-General for Budget'),
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/AGRI',
    ]);
    $this->drupalCreateNode([
      'type' => 'oe_publication',
      'title' => 'Publication node in Person',
    ]);

    // Create a "Person" content, mandatory fields first.
    $this->drupalGet('/node/add/oe_person');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $assert_session->elementNotExists('css', 'input[name="title[0][value]"]');

    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('first name form element', 'Content limited to 125 characters, remaining: 125');
    $this->assertRegionText('last name form element', 'Content limited to 125 characters, remaining: 125');

    $page->fillField('Subject tags', 'financing');
    $page->fillField('Teaser', 'Teaser text');
    $page->selectFieldOption('What type of person are you adding?', 'EU institutions related person');
    $page->fillField('First name', 'Firstname');
    $page->fillField('Last name', 'Lastname');
    $this->assertSelectOptions('Gender', [
      '- Select a value -',
      'female',
      'male',
      'not stated',
    ]);
    $page->selectFieldOption('Gender', 'not stated');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Person Firstname Lastname has been created.');
    $assert_session->pageTextContains('Firstname Lastname');
    $assert_session->linkExists('financing');
    $assert_session->pageTextContains('Teaser text');
    $assert_session->pageTextContains('EU institutions related person');
    $assert_session->pageTextContains('Firstname');
    $assert_session->pageTextContains('Lastname');
    $assert_session->pageTextContains('not stated');
    $assert_session->linkExists('Committee on Agriculture and Rural Development');

    // Optional fields.
    $node = $this->getNodeByTitle('Firstname Lastname');
    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->fillField('Description', 'Description Description text');
    $page->fillField('Introduction', 'Summary text');
    $page->fillField('Navigation title', 'Navi title');
    $page->fillField('Alternative title', 'Shorter title');
    $page->fillField('Displayed name', 'Altered name');
    $this->fillFieldInRegion('Person portrait photo', 'Use existing media', 'Image 1');
    $this->fillFieldInRegion('Person Media', 'Use existing media', 'Image 2');
    $page->fillField('Departments', 'European Patent Office');
    $page->fillField('Transparency introduction', 'transparency-introduction text');
    $this->fillFieldInRegion('Person transparency links', 'URL', 'http://transparency.example.com');
    $this->fillFieldInRegion('Person transparency links', 'Link text', 'Example link');
    $page->fillField('Biography introduction', 'Bio-intro');

    $biography = $page->find('css', '.field--name-oe-person-biography-timeline');
    $this->fillCollapsibleRow($biography, 1, [
      'Label' => 'Label 1',
      'Title' => 'Title 1',
      'Content' => 'Body 1',
    ]);
    $this->pressButtonInRegion('Person biography', 'Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillCollapsibleRow($biography, 2, [
      'Label' => 'Label 2',
      'Title' => 'Title 2',
      'Content' => 'Body 2',
    ]);
    $this->pressButtonInRegion('Person biography', 'Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillCollapsibleRow($biography, 3, [
      'Label' => 'Label 3',
      'Title' => 'Title 3',
      'Content' => 'Body 3',
    ]);

    $this->fillFieldInRegion('Person CV upload', 'Use existing media', 'My Document 1');
    $page->fillField('Declaration of interests introduction', 'declaration text');
    $this->fillFieldInRegion('Person declaration of interests file', 'Use existing media', 'My Document 2');
    $this->fillFieldInRegion('Social media links', 'URL', 'http://twitter.com');
    $this->fillFieldInRegion('Social media links', 'Link text', 'X');
    $page->selectFieldOption('Link type', 'X');
    $page->fillField('Redirect link', 'http://example.com');

    // Contact field.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Person contacts', 'Name', 'Name of the contact');
    $this->fillFieldInRegion('Person contacts', 'Organisation', 'Person contact organisation');
    $this->fillFieldInRegion('Person contacts', 'Body text', 'Person contact body text');
    $this->fillFieldInRegion('Person contacts', 'Website', 'http://www.example.com/person_contact');
    $this->fillFieldInRegion('Person contacts', 'Email', 'person_contact@example.com');
    $this->fillFieldInRegion('Person contacts', 'Phone number', '0488779033');
    $this->fillFieldInRegion('Person contacts', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('Person contacts', 'Fax number', '0488779035');
    $this->selectFieldOptionInRegion('Person contacts', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Person contacts', 'Street address', 'Person contact street');
    $this->fillFieldInRegion('Person contacts', 'Postal code', '9000');
    $this->fillFieldInRegion('Person contacts', 'City', 'Budapest');
    $this->fillFieldInRegion('Person contacts', 'Office', 'Person contact office');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:person_contact_social@example.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'Person contact social link email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('Person contacts', 'Media item', 'Contact image');
    $this->fillFieldInRegion('Person contacts', 'Caption', 'Person contact caption');
    $this->fillFieldInRegion('Person contacts', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');
    $page->pressButton('Create contact');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Add an organisation as contact.
    $this->selectSingleOptionInRegion('Person contacts', 'Organisation');
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Person contacts', 'Name', 'Organisation contact name');
    $this->fillFieldInRegion('Person contacts', 'Organisation', 'Organisation as a contact');

    // Create a document reference to a Document media.
    $page->pressButton('Add new document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Person documents', 'Use existing media', 'My Document 3');
    $page->pressButton('Create document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Create a document reference to a Publication node.
    $this->selectSingleOptionInRegion('Person documents', 'Publication');
    $page->pressButton('Add new document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Person documents', 'Publication', 'Publication node in Person');
    $page->pressButton('Create document reference');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Jobs field.
    $page->pressButton('Add new person job');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->fillField('oe_person_jobs[form][0][oe_role_reference][0][target_id]', 'Adviser');
    $page->fillField('Responsibilities assigned to the job', 'Responsibilities text');
    $page->checkField('Acting role');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Altered name');
    $assert_session->pageTextContains('Navi title');
    $assert_session->pageTextContains('Shorter title');
    $assert_session->pageTextContains('Image 1');
    $assert_session->pageTextContains('Image 2');
    $assert_session->pageTextContains('European Patent Office');
    $assert_session->pageTextContains('transparency-introduction text');
    $assert_session->pageTextContains('Bio-intro');
    $assert_session->pageTextContains('Label 1');
    $assert_session->pageTextContains('Title 1');
    $assert_session->pageTextContains('Body 1');
    $assert_session->pageTextContains('Label 2');
    $assert_session->pageTextContains('Title 2');
    $assert_session->pageTextContains('Body 2');
    $assert_session->pageTextContains('Label 3');
    $assert_session->pageTextContains('Title 3');
    $assert_session->pageTextContains('Body 3');
    $assert_session->pageTextContains('My Document 1');
    $assert_session->pageTextContains('declaration text');
    $assert_session->pageTextContains('My Document 2');
    $assert_session->linkExists('X');
    $assert_session->pageTextContains('http://example.com');
    $assert_session->linkExists('Example link');
    $assert_session->pageTextContains('Description Description text');

    // Person contacts values.
    $assert_session->pageTextContains('Name of the contact');
    $assert_session->pageTextContains('Person contact body text');
    $assert_session->pageTextContains('Person contact organisation');
    $assert_session->linkExists('http://www.example.com/person_contact');
    $assert_session->pageTextContains('person_contact@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->pageTextContains('Person contact street');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->linkExists('Person contact social link email');
    $assert_session->pageTextContains('Person contact office');
    $assert_session->linkExists('Contact image');
    $assert_session->pageTextContains('Person contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');
    $assert_session->pageTextContains('Organisation contact name');

    // The document references are shown.
    $assert_session->pageTextContains('document2.pdf');
    $assert_session->pageTextContains('Publication node in Person');
    $assert_session->pageTextContains('Adviser');
    $assert_session->pageTextContains('Responsibilities text');
    $this->assertStringContainsString('On', $this->getSession()->getPage()->find('css', '.field--name-oe-acting')->getText());

    // Changing the type of person invalidates the role of the job.
    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->selectFieldOption('What type of person are you adding?', 'Person not part of the EU institutions');
    $page->pressButton('Save');
    $assert_session->pageTextContains('The role "(Acting) Adviser" is not compatible with the type of person currently selected. Please edit the related job entry and fix its role accordingly.');

    $page = $this->getSession()->getPage();
    $page->fillField('Organisation', 'Organisation demo page');
    $this->pressButtonInRegion('Person jobs', 'Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->fillField('Role (free text)', 'Person job role');
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Organisation demo page');
    $assert_session->linkNotExists('European Patent Office');
    $assert_session->pageTextNotContains('Adviser');
    $assert_session->pageTextContains('Person job role');
    $assert_session->pageTextNotContains('Acting role');

    // Create another person to assert that the length limited fields truncate
    // the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_person');
    $page = $this->getSession()->getPage();
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->selectFieldOption('What type of person are you adding?', 'EU institutions related person');
    $page->fillField('First name', 'Firstname');
    $page->fillField('Last name', 'Lastname');
    $page->selectFieldOption('Gender', 'male');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->pressButton('Save');

    $this->assertCommonFieldsAreTruncated();
  }

  /**
   * Tests that removing the referenced entities does not delete them.
   */
  public function testPersonReferencedEntitiesRemoval(): void {
    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $person_job = $this->createDefaultPersonJob([
      'oe_role_reference' => $this->getReferenceTargetIds('oe_person_job', 'oe_default', 'oe_role_reference', 'Adviser'),
      'oe_acting' => 1,
      'oe_description' => 'Responsibilities text',
    ]);
    $document = $this->createDocumentMedia('My Document', 'sample.pdf');
    $document_reference = $this->createDocumentReference('oe_document', [
      'oe_document' => $document,
    ]);
    $node = $this->drupalCreateNode([
      'type' => 'oe_person',
      'oe_person_contacts' => $contact,
      'oe_person_type' => 'eu',
      'oe_person_first_name' => 'First',
      'oe_person_last_name' => 'Last',
      'oe_person_gender' => $this->getReferenceTargetIds('node', 'oe_person', 'oe_person_gender', 'not stated'),
      'oe_person_jobs' => $person_job,
      'oe_person_documents' => $document_reference,
      'oe_subject' => 'http://data.europa.eu/uxp/1010',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/ACER',
    ]);

    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'view published skos concept entities',
      'view published oe_contact',
    ]));
    $this->drupalGet($node->toUrl());

    $assert_session = $this->assertSession();
    $assert_session->pageTextContains('First Last');
    $assert_session->pageTextContains('A general contact');
    $assert_session->pageTextContains('Adviser');
    $assert_session->pageTextContains('sample.pdf');

    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'create oe_person content',
      'edit any oe_person content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]));
    $this->drupalGet($node->toUrl('edit-form'));

    $this->pressButtonInRegion('Person contacts', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('Person contacts', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->pressButtonInRegion('Person jobs', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove (Acting) Adviser?');
    $this->pressButtonInRegion('Person jobs', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->pressButtonInRegion('Person documents', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove My Document?');
    $this->pressButtonInRegion('Person documents', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Person First Last has been updated.');
    $assert_session->pageTextNotContains('A general contact');
    $assert_session->pageTextNotContains('Adviser');
    $assert_session->pageTextNotContains('sample.pdf');
    $this->assertEntityExists($contact);
    $this->assertEntityExists($person_job);
    $this->assertEntityExists($document_reference);
  }

}
