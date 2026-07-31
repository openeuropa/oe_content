<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_event\FunctionalJavascript;

use Drupal\Tests\oe_content\FunctionalJavascript\ContentTestBase;

/**
 * Tests the creation and the editing of Event content through the UI.
 *
 * @group oe_content
 * @group batch1
 */
class EventContentTest extends ContentTestBase {

  /**
   * Tests that the fields of the creation form are grouped logically.
   */
  public function testEventFieldGroups(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_event content',
      'edit own oe_event content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_event');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // The text assertions are actually checking for fields.
    $assert_session->pageTextContains('Type');
    $assert_session->pageTextContains('Page title');
    $assert_session->pageTextContains('Description summary');
    $assert_session->pageTextContains('Subject tags');
    $assert_session->pageTextContains('Start date');
    $assert_session->pageTextContains('End date');
    $assert_session->pageTextContains('Online only');
    $assert_session->pageTextContains('Status');
    $assert_session->pageTextContains('Status description');
    $assert_session->pageTextContains('Languages');
    $assert_session->pageTextContains('Who should attend');
    $assert_session->pageTextContains('Event website');
    $assert_session->pageTextContains('Link type');

    // The registration group is collapsed by default.
    $assert_session->pageTextContains('Registration');
    $assert_session->pageTextNotContains('Registration URL');
    $assert_session->pageTextNotContains('Registration date');
    $assert_session->pageTextNotContains('Entrance fee');
    $assert_session->pageTextNotContains('Registration capacity');
    $page->pressButton('Registration');
    $assert_session->pageTextContains('Registration URL');
    $assert_session->pageTextContains('Registration date');
    $assert_session->pageTextContains('Entrance fee');
    $assert_session->pageTextContains('Registration capacity');

    // The venue group is open by default.
    $assert_session->pageTextContains('Venue');
    $page->pressButton('Add new venue');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Name');
    $assert_session->pageTextContains('Capacity');
    $assert_session->pageTextContains('Room');
    $assert_session->pageTextContains('Country');

    // The online group is collapsed by default.
    $assert_session->pageTextContains('Online');
    $assert_session->pageTextNotContains('Online type');
    $assert_session->pageTextNotContains('Online time');
    $assert_session->pageTextNotContains('Online description');
    $assert_session->pageTextNotContains('Online link');
    $page->pressButton('Online');
    $assert_session->pageTextContains('Online type');
    $assert_session->pageTextContains('Online time');
    $assert_session->pageTextContains('Online description');
    $assert_session->pageTextContains('Online link');

    // The organiser group is opened by default.
    $assert_session->pageTextContains('Organiser');
    $assert_session->pageTextContains('Organiser is internal');
    $this->assertVisuallyVisible($page->find('css', '#edit-oe-event-organiser-internal-0-target-id'));
    $assert_session->pageTextNotContains('Organiser name');
    $page->uncheckField('Organiser is internal');
    $assert_session->pageTextContains('Organiser name');
    $this->assertNotVisuallyVisible($page->find('css', '#edit-oe-event-organiser-internal-0-target-id'));

    $this->assertRegionText('Event media', 'Media');

    // The full description group is opened by default.
    $assert_session->pageTextContains('Full description');
    $assert_session->pageTextContains('Featured media');
    $assert_session->pageTextContains('Featured media legend');
    $assert_session->pageTextContains('Full text');

    // The full report group is collapsed by default.
    $assert_session->pageTextContains('Event report');
    $assert_session->pageTextNotContains('Report text');
    $assert_session->pageTextNotContains('Summary for report');
    $assert_session->pageTextNotContains('Main link to further media items');
    $assert_session->pageTextNotContains('Other links to further media items');
    $page->pressButton('Event report');
    $assert_session->pageTextContains('Report text');
    $assert_session->pageTextContains('Summary for report');
    $this->assertRegionText('Event report', 'Main link to further media items');
    $this->assertRegionText('Event report', 'Other links to further media items');

    // The Event contact field group contains the expected fields.
    $assert_session->pageTextContains('Event contact');
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertRegionText('Event contact', 'Name');
    $this->assertRegionText('Event contact', 'Organisation');
    $this->assertRegionText('Event contact', 'Body text');
    $this->assertRegionText('Event contact', 'Website');
    $this->assertRegionText('Event contact', 'Email');
    $this->assertRegionText('Event contact', 'Phone number');
    $this->assertRegionText('Event contact', 'Mobile number');
    $this->assertRegionText('Event contact', 'Fax number');
    $this->assertRegionText('Event contact', 'Country');
    $this->assertRegionText('Event contact', 'Office');
    $this->assertRegionText('Event contact', 'Social media links');
    $this->assertRegionText('Event contact', 'Image');
    $this->assertRegionText('Event contact', 'Press contacts');

    // The alternative titles and teaser group is open by default.
    $assert_session->pageTextContains('Alternative titles and teaser');
    $assert_session->pageTextContains('Alternative title');
    $assert_session->pageTextContains('Use this field to create an alternative title for use in the URL and in list views. If the page title is longer than 60 characters, you can add a shorter title here.');
    $assert_session->pageTextContains('Navigation title');
    $assert_session->pageTextContains('When filled in, the navigation title will replace the page title in the breadcrumb, horizontal menu and navigation blocks.');
    $assert_session->pageTextContains('Teaser');

    // The event programme group is open by default.
    $assert_session->pageTextContains('Programme');
    $page->pressButton('Add new Programme');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertRegionText('Event programme', 'Name');
    $this->assertRegionText('Event programme', 'Description');
    $this->assertRegionText('Event programme', 'Start/end date');

    // Metadata fields are visible.
    $assert_session->pageTextContains('Content owner');
    $assert_session->pageTextContains('Responsible department');
    $assert_session->pageTextContains('Language');
  }

  /**
   * Tests the creation of an Event content through the UI.
   */
  public function testEventCreation(): void {
    \Drupal::service('module_installer')->install([
      'media_avportal_mock',
      'oe_media_avportal_test',
    ]);

    \Drupal::service('theme_installer')->install([
      'olivero',
    ]);
    \Drupal::configFactory()->getEditable('system.theme')->set('default', 'olivero')->save();

    $user = $this->drupalCreateUser([
      'access content',
      'create oe_event content',
      'edit any image media',
      'edit own oe_event content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->createImageMedia('Contact image', 'example_1.jpeg', 'Contact image alternative text');
    $this->createImageMedia('Media image', 'example_2.jpeg', 'Media alternative text');
    $this->createAvPortalPhotoMedia('https://audiovisual.ec.europa.eu/en/photo/P-038924~2F00-15');
    $this->createAvPortalPhotoMedia('https://audiovisual.ec.europa.eu/en/photo/P-039321~2F00-04');

    $this->drupalGet('/node/add/oe_event');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $this->assertRegionText('title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('summary form element', 'Content limited to 250 characters, remaining: 250');
    $this->assertRegionText('featured media legend form element', 'Content limited to 150 characters, remaining: 150');
    $this->assertRegionText('alternative title form element', 'Content limited to 170 characters, remaining: 170');
    $this->assertRegionText('teaser form element', 'Content limited to 300 characters, remaining: 300');

    $page->selectFieldOption('Type', 'Info days');
    $page->fillField('Page title', 'My Event item');

    // Registration field group.
    $page->pressButton('Registration');
    $page->fillField('Registration URL', 'http://example.com');
    $this->fillDateRangeTimezoneField('Start date', 'Registration date', '23-02-2019 02:30', 'Europe/Brussels');
    $this->fillDateRangeTimezoneField('End date', 'Registration date', '23-02-2019 14:30', 'Europe/Brussels');
    $page->fillField('Entrance fee', 'Free of charge');
    $page->fillField('Registration capacity', '100 seats');

    $page->fillField('Description summary', 'Description summary text');
    $page->fillField('Subject tags', 'EU financing');
    $this->fillDateRangeTimezoneField('Start date', 'Event date', '21-02-2019 02:15', 'Europe/Brussels');
    $this->fillDateRangeTimezoneField('End date', 'Event date', '21-02-2019 14:15', 'Europe/Brussels');
    $page->checkField('Online only');

    // Venue reference by inline entity form.
    $page->pressButton('Add new venue');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Event venue', 'Name', 'Name of the venue');
    $this->fillFieldInRegion('Event venue', 'Capacity', 'Capacity of the venue');
    $this->fillFieldInRegion('Event venue', 'Room', 'Room of the venue');
    $this->selectFieldOptionInRegion('Event venue', 'Country', 'Belgium');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Event venue', 'Street address', 'Rue belliard 28');
    $this->fillFieldInRegion('Event venue', 'Postal code', '1000');
    $this->fillFieldInRegion('Event venue', 'City', 'Brussels');
    $page->pressButton('Create venue');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Online field group.
    $page->pressButton('Online');
    $this->assertSelectOptions('Online type', [
      '- None -',
      'Facebook',
      'Livestream',
    ]);
    $page->selectFieldOption('Online type', 'Facebook');
    $this->fillDateRangeTimezoneField('Start date', 'Online time', '22-02-2019 02:30', 'Europe/Brussels');
    $this->fillDateRangeTimezoneField('End date', 'Online time', '22-02-2019 14:30', 'Europe/Brussels');
    $page->fillField('Online description', 'Online description text');
    $this->fillFieldInRegion('Online link', 'URL', 'http://ec.europa.eu/2');
    $this->fillFieldInRegion('Online link', 'Link text', 'Online link');

    $this->assertSelectOptions('Status', [
      'As planned',
      'Cancelled',
      'Rescheduled',
      'Postponed',
    ]);
    $page->selectFieldOption('Status', 'As planned');
    $page->fillField('Status description', 'Status description message');
    $page->fillField('Languages', 'Hungarian');
    $page->fillField('Who should attend', 'Types of audiences that this event targets');

    // Organiser field group.
    $page->uncheckField('Organiser is internal');
    $page->fillField('Organiser name', 'Organiser name');

    // Event website field group.
    $this->fillFieldInRegion('Website', 'URL', 'http://ec.europa.eu');
    $this->fillFieldInRegion('Website', 'Link text', 'Website');

    // Add a social media link.
    $this->fillFieldInRegion('Social media links', 'URL', 'http://twitter.com');
    $this->fillFieldInRegion('Social media links', 'Link text', 'X');
    $this->assertSelectOptions('Link type', [
      '- Select -',
      'Bluesky',
      'Email',
      'Facebook',
      'Flickr',
      'Google+',
      'Instagram',
      'Linkedin',
      'Mastodon',
      'Pinterest',
      'RSS',
      'Storify',
      'Telegram',
      'Threads',
      'X',
      'Yammer',
      'YouTube',
      'Other',
    ]);
    $page->selectFieldOption('Link type', 'X');

    // Add a media item.
    $this->fillFieldInRegion('Event media', 'Use existing media', 'Media image');

    // Description field group.
    $this->fillFieldInRegion('Description', 'Use existing media', 'Euro with miniature figurines');
    $page->fillField('Featured media legend', 'Euro with miniature figurines');
    $page->fillField('Full text', 'Full text paragraph');

    // Report field group.
    $page->pressButton('Event report');
    $page->fillField('Report text', 'Report text paragraph');
    $page->fillField('Summary for report', 'Report summary text');
    $this->fillFieldInRegion('Event report', 'URL', '<front>');
    $this->fillFieldInRegion('Event report', 'Link text', 'More media items');
    $this->fillFieldInRegion('Event report', 'Other links to further media items', 'More links to media items');

    // Event contact field group.
    $page->pressButton('Add new contact');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Event contact', 'Name', 'Name of the event contact');
    $this->fillFieldInRegion('Event contact', 'Organisation', 'Event contact organisation');
    $this->fillFieldInRegion('Event contact', 'Body text', 'Event contact body text');
    $this->fillFieldInRegion('Event contact', 'Website', 'http://www.example.com/event_contact');
    $this->fillFieldInRegion('Event contact', 'Email', 'test@example.com');
    $this->fillFieldInRegion('Event contact', 'Phone number', '0488779033');
    $this->fillFieldInRegion('Event contact', 'Mobile number', '0488779034');
    $this->fillFieldInRegion('Event contact', 'Fax number', '0488779035');
    $this->selectFieldOptionInRegion('Event contact', 'Country', 'Hungary');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->fillFieldInRegion('Event contact', 'Street address', 'Back street 3');
    $this->fillFieldInRegion('Event contact', 'Postal code', '9000');
    $this->fillFieldInRegion('Event contact', 'City', 'Budapest');
    $this->fillFieldInRegion('Event contact', 'Office', 'Event contact office');
    $this->fillFieldInRegion('Contact social media links', 'URL', 'mailto:example@email.com');
    $this->fillFieldInRegion('Contact social media links', 'Link text', 'Event contact social link email');
    $this->selectFieldOptionInRegion('Contact social media links', 'Link type', 'Email');
    $this->fillFieldInRegion('Event contact', 'Media item', 'Contact image');
    $this->fillFieldInRegion('Event contact', 'Caption', 'Event contact caption');
    $this->fillFieldInRegion('Event contact', 'Press contacts', 'http://example.com/press_contacts');
    $this->fillFieldInRegion('Contact link', 'URL', 'https://www.example.com/link');
    $this->fillFieldInRegion('Contact link', 'Link text', 'Contact link');

    // The event programme field group.
    $page->pressButton('Add new Programme');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertRegionText('Programme name', 'Content limited to 150 characters, remaining: 150');
    $this->fillFieldInRegion('Event programme', 'Name', 'Event programme');
    $this->fillFieldInRegion('Event programme', 'Description', 'Event programme description');
    $this->fillDateRangeTimezoneField('Start date', 'Start/end date', '21-10-2021 02:15', 'Europe/Brussels');
    $this->fillDateRangeTimezoneField('End date', 'Start/end date', '21-10-2021 14:15', 'Europe/Brussels');

    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Responsible department', 'Audit Board of the European Communities');
    $page->fillField('Teaser', 'Event teaser');
    $page->pressButton('Save');

    $assert_session->pageTextContains('Event My Event item has been created.');
    $assert_session->pageTextContains('My Event item');
    $assert_session->pageTextContains('Full text paragraph');
    $assert_session->pageTextContains('Thu, 21 Feb 2019 - 02:15');
    $assert_session->pageTextContains('Thu, 21 Feb 2019 - 14:15');
    $assert_session->pageTextContains('Info days');
    $assert_session->pageTextContains('Hungarian');
    $assert_session->pageTextContains('Types of audiences that this event targets');
    $assert_session->pageTextContains('As planned');
    $assert_session->pageTextContains('Status description message');
    $assert_session->linkExists('Website');
    $assert_session->linkExists('X');
    $assert_session->pageTextContains('Facebook');
    $assert_session->pageTextContains('Media image');
    $assert_session->pageTextContains('Online description text');
    $assert_session->pageTextContains('Fri, 22 Feb 2019 - 02:30');
    $assert_session->pageTextContains('Fri, 22 Feb 2019 - 14:30');
    $assert_session->linkExists('Online link');
    $assert_session->pageTextContains('Organiser name');
    $assert_session->pageTextContains('Description summary text');
    $assert_session->pageTextContains('Euro with miniature figurines');
    $assert_session->pageTextContains('Report summary text');
    $assert_session->pageTextContains('Report text paragraph');
    $assert_session->linkExists('More media items');
    $assert_session->pageTextContains('More links to media items');
    $assert_session->linkExists('http://example.com');
    $assert_session->pageTextContains('Sat, 23 Feb 2019 - 02:30');
    $assert_session->pageTextContains('Sat, 23 Feb 2019 - 14:30');
    $assert_session->pageTextContains('Free of charge');
    $assert_session->pageTextContains('100 seats');

    // Venue entity values.
    $assert_session->pageTextContains('Name of the venue');
    $assert_session->pageTextContains('Capacity of the venue');
    $assert_session->pageTextContains('Room of the venue');
    $assert_session->pageTextContains('Rue belliard 28');
    $assert_session->pageTextContains('1000 Brussels');
    $assert_session->pageTextContains('Belgium');

    // Event contact values.
    $assert_session->pageTextContains('Name of the event contact');
    $assert_session->pageTextContains('Event contact body text');
    $assert_session->pageTextContains('Event contact organisation');
    $assert_session->linkExists('http://www.example.com/event_contact');
    $assert_session->pageTextContains('test@example.com');
    $assert_session->pageTextContains('0488779033');
    $assert_session->pageTextContains('0488779034');
    $assert_session->pageTextContains('0488779035');
    $assert_session->pageTextContains('Back street 3');
    $assert_session->pageTextContains('Budapest');
    $assert_session->pageTextContains('9000');
    $assert_session->pageTextContains('Hungary');
    $assert_session->linkExists('Event contact social link email');
    $assert_session->pageTextContains('Event contact office');
    $assert_session->linkExists('Contact image');
    $assert_session->pageTextContains('Event contact caption');
    $assert_session->linkExists('http://example.com/press_contacts');
    $assert_session->linkExists('Contact link');

    // Event programme values.
    $assert_session->pageTextContains('Event programme');
    $assert_session->pageTextContains('Event programme description');
    $assert_session->pageTextContains('Thu, 21 Oct 2021 - 02:15');
    $assert_session->pageTextContains('Thu, 21 Oct 2021 - 14:15');

    // Create another event to assert that the length limited fields truncate
    // the characters exceeding their limit.
    $this->drupalGet('/node/add/oe_event');
    $page = $this->getSession()->getPage();
    $page->fillField('Page title', 'My long Event');
    $page->selectFieldOption('Type', 'Info days');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Description summary', 'Description summary text');
    $this->fillFieldInRegion('Description', 'Use existing media', 'Euro with miniature figurines');
    $page->fillField('Featured media legend', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendr Featured media legend. Text to remove');
    $page->fillField('Full text', 'Full text paragraph');
    $page->fillField('Teaser', self::LONG_TEASER);
    $page->fillField('Introduction', self::LONG_INTRODUCTION);
    $page->fillField('Alternative title', self::LONG_ALTERNATIVE_TITLE);
    $page->fillField('Subject tags', 'financing');
    $page->fillField('Responsible department', 'European Patent Office');
    $page->fillField('Languages', 'English');
    $page->pressButton('Save');

    $assert_session->pageTextContains('hendr Featured media legend.');
    $this->assertCommonFieldsAreTruncated();
  }

  /**
   * Tests the validation of the required fields of the Event content type.
   */
  public function testEventRequiredFieldsValidation(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_event content',
      'edit own oe_event content',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/oe_event');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    $page->fillField('Page title', 'My Event item');
    $page->selectFieldOption('Status', 'As planned');
    $page->fillField('Languages', 'Hungarian');
    $page->selectFieldOption('Type', 'Info days');
    $page->fillField('Subject tags', 'EU financing');
    $page->fillField('Content owner', 'Committee on Agriculture and Rural Development');
    $page->fillField('Responsible department', 'Audit Board of the European Communities');
    $page->fillField('Teaser', 'Event teaser');

    // Make sure that one value is saved, even if both are filled in.
    $page->checkField('Organiser is internal');
    $page->fillField('Internal organiser', 'Audit Board of the European Communities');
    $page->uncheckField('Organiser is internal');
    $page->fillField('Organiser name', 'Organiser external');
    $page->pressButton('Save');
    $assert_session->pageTextContains('Organiser name Organiser external');
    $assert_session->pageTextNotContains('Internal organiser Audit Board of the European Communities');

    $node = $this->getNodeByTitle('My Event item');
    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->uncheckField('Organiser is internal');
    $page->fillField('Organiser name', 'Organiser external');
    $page->checkField('Organiser is internal');
    $page->fillField('Internal organiser', 'Audit Board of the European Communities');
    $page->pressButton('Save');
    $assert_session->pageTextNotContains('Organiser name Organiser external');
    $assert_session->pageTextContains('Internal organiser Audit Board of the European Communities');

    // Make sure that the validation of the Online fields group works.
    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->pressButton('Online');
    $page->selectFieldOption('Online type', 'Facebook');
    $page->pressButton('Save');
    $assert_session->statusMessageContains('Online time field is required.', 'error');
    $assert_session->statusMessageContains('Online link field is required.', 'error');

    // Make sure that the errors related to the Online fields are fixed.
    $this->fillDateRangeTimezoneField('Start date', 'Online time', '22-02-2019 02:30', 'Europe/Brussels');
    $this->fillDateRangeTimezoneField('End date', 'Online time', '22-02-2019 14:30', 'Europe/Brussels');
    $this->getSession()->getPage()->fillField('Online description', 'Online description text');
    $this->fillFieldInRegion('Online link', 'URL', 'http://ec.europa.eu/2');
    $this->fillFieldInRegion('Online link', 'Link text', 'Online link');
    $this->getSession()->getPage()->pressButton('Save');
    $assert_session->statusMessageContains('Event My Event item has been updated.', 'status');

    // Make sure that the validation of the Registration fields group works.
    $this->drupalGet($node->toUrl('edit-form'));
    $page = $this->getSession()->getPage();
    $page->pressButton('Registration');
    $this->fillDateRangeTimezoneField('Start date', 'Registration date', '23-02-2019 02:15', 'Europe/Brussels');
    $this->fillDateRangeTimezoneField('End date', 'Registration date', '23-02-2019 14:15', 'Europe/Brussels');
    $this->getSession()->getPage()->fillField('Registration capacity', '100');
    $this->getSession()->getPage()->pressButton('Save');
    $assert_session->statusMessageContains('Registration URL field is required.', 'error');

    // Make sure that the errors related to the Registration fields are fixed.
    $this->getSession()->getPage()->fillField('Registration URL', 'http://example.com');
    $this->getSession()->getPage()->pressButton('Save');
    $assert_session->statusMessageContains('Event My Event item has been updated.', 'status');

    // Make sure that the validation of the Social media links works.
    $this->drupalGet($node->toUrl('edit-form'));
    $this->fillFieldInRegion('Social media links', 'URL', 'htt://twitter.com');
    $this->fillFieldInRegion('Social media links', 'Link text', 'X');
    $this->getSession()->getPage()->selectFieldOption('Link type', 'X');
    $this->getSession()->getPage()->pressButton('Save');
    $assert_session->statusMessageContains("The path 'htt://twitter.com' is invalid.", 'error');

    // Make sure that the errors related to the Social media links are fixed.
    $this->fillFieldInRegion('Social media links', 'URL', 'http://twitter.com');
    $this->getSession()->getPage()->pressButton('Save');
    $assert_session->statusMessageContains('Event My Event item has been updated.', 'status');
  }

  /**
   * Tests that removing a venue and a contact only removes the references.
   */
  public function testVenueAndContactRemoval(): void {
    $user = $this->drupalCreateUser([
      'access content',
      'create oe_event content',
      'edit any oe_event content',
      'manage corporate content entities',
      'view published skos concept entities',
    ]);
    $this->drupalLogin($user);

    $venue = $this->createDefaultVenue(['name' => 'A venue']);
    $contact = $this->createGeneralContact(['name' => 'A general contact']);
    $node = $this->drupalCreateNode([
      'type' => 'oe_event',
      'title' => 'Event demo page',
      'oe_event_type' => $this->getReferenceTargetIds('node', 'oe_event', 'oe_event_type', 'Exhibitions'),
      'oe_summary' => 'Event introduction text',
      'oe_event_languages' => $this->getReferenceTargetIds('node', 'oe_event', 'oe_event_languages', 'Valencian'),
      'oe_event_dates' => [
        'value' => '2019-02-21T02:21:00',
        'end_value' => '2019-02-21T14:21:00',
      ],
      'oe_event_status' => 'as_planned',
      'oe_teaser' => 'Event teaser',
      'oe_event_venue' => $venue,
      'oe_event_contact' => $contact,
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
    ]);

    $this->drupalGet($node->toUrl('edit-form'));
    $assert_session = $this->assertSession();

    $this->pressButtonInRegion('Event venue', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove A venue?');
    $this->pressButtonInRegion('Event venue', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->pressButtonInRegion('Event contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $assert_session->pageTextContains('Are you sure you want to remove A general contact?');
    $this->pressButtonInRegion('Event contact', 'Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');

    $assert_session->pageTextContains('Event Event demo page has been updated.');
    $this->assertEntityExists($venue);
    $this->assertEntityExists($contact);
  }

}
