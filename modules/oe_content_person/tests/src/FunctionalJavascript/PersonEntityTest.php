<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_person\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Functional tests for the Person content type.
 */
class PersonEntityTest extends WebDriverTestBase {

  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_person',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();
  }

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
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_reference][0][target_id]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_acting][value]')->isVisible());
    $this->assertTrue($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_description][0][value]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_name][0][value]')->isVisible());

    // Assert the job required fields.
    $this->assertEquals('required', $this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_role_reference][0][target_id]')->getAttribute('required'));
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][0][oe_acting][value]')->hasAttribute('required'));

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
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_name][0][value]')->hasAttribute('required'));
    $this->assertEquals('required', $this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->getAttribute('required'));

    // Assert the job does not have a reference role.
    $this->assertEmpty($this->getSession()->getPage()->findField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]')->getValue());

    // Update the job with a reference role and set it to be an acting role.
    $this->getSession()->getPage()->fillField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');
    $this->getSession()->getPage()->checkField('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_acting][value]');

    // Save the person and assert it was updated.
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Person John Doe has been updated.');

    // Edit the node again and assert the job values where updated.
    $this->drupalGet('/node/1/edit');
    $job_region = $this->getSession()->getPage()->find('css', '#edit-oe-person-jobs-wrapper');
    $job_region->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->fieldValueEquals('oe_person_jobs[form][inline_entity_form][entities][0][form][oe_role_reference][0][target_id]', 'Adviser (http://publications.europa.eu/resource/authority/role-qualifier/ADVIS)');

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
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_name][0][value]')->isVisible());
    $this->assertFalse($this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_name][0][value]')->hasAttribute('required'));
    $this->assertEquals('required', $this->getSession()->getPage()->findField('oe_person_jobs[form][1][oe_role_reference][0][target_id]')->getAttribute('required'));
  }

}
