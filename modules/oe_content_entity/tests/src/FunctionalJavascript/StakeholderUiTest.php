<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\FunctionalJavascript;

use Drupal\Tests\BrowserTestBase;

/**
 * Test Stakeholder bundle.
 */
class StakeholderUiTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
  ];

  /**
   * Tests Stakeholder creation.
   */
  public function testStakeholderBundleUi(): void {
    $user = $this->drupalCreateUser([
      'manage corporate content entities',
      'access administration pages',
      'create oe_organisation oe_stakeholder corporate entity',
      'edit oe_organisation oe_stakeholder corporate entity',
      'access oe_organisation canonical page',
    ]);

    $this->drupalLogin($user);

    $this->drupalGet("/admin/content/oe_organisation/add/oe_stakeholder");

    $this->getSession()->getPage()->fillField('Name', "My stakeholder");
    $acronym_value = "My Acronym";
    $company_value = "Best company";
    $country = "HU";
    $street_address_value = "My street 20.";
    $city_value = "Budapest";
    $postal_code_value = "1171";
    $website_value = "https://test.com";
    $contact_value = "https://test2.com";
    $this->getSession()->getPage()->fillField('Acronym', $acronym_value);
    $this->getSession()->getPage()->fillField('Country', $country);
    $this->getSession()->getPage()->fillField('Company', $company_value);
    $this->getSession()->getPage()->fillField('Street address', $street_address_value);
    $this->getSession()->getPage()->fillField('City', $city_value);
    $this->getSession()->getPage()->fillField('Postal code', $postal_code_value);
    $this->getSession()->getPage()->fillField('Website', $website_value);
    $this->getSession()->getPage()->fillField('Contact page URL', $contact_value);
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains("Created the My stakeholder.");

    $this->drupalGet("/admin/content/oe_organisation/1/edit");
    $this->assertSession()->pageTextContains("My stakeholder");
    $acronym = $this->assertSession()->fieldExists('oe_acronym[0][value]')->getValue();
    $company = $this->assertSession()->fieldExists('oe_address[0][address][organization]')->getValue();
    $street_address = $this->assertSession()->fieldExists('oe_address[0][address][address_line1]')->getValue();
    $city = $this->assertSession()->fieldExists('oe_address[0][address][locality]')->getValue();
    $postal_code = $this->assertSession()->fieldExists('oe_address[0][address][postal_code]')->getValue();
    $website = $this->assertSession()->fieldExists('oe_website[0][uri]')->getValue();
    $contact = $this->assertSession()->fieldExists('oe_contact_url[0][uri]')->getValue();
    $this->assertTrue($acronym == $acronym_value);
    $this->assertTrue($company == $company_value);
    $this->assertTrue($street_address == $street_address_value);
    $this->assertTrue($city == $city_value);
    $this->assertTrue($postal_code == $postal_code_value);
    $this->assertTrue($website == $website_value);
    $this->assertTrue($contact == $contact_value);
    $this->assertOptionSelected('Country', $country);
  }

}
