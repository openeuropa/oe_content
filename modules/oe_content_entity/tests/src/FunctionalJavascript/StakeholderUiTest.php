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
    ]);

    $this->drupalLogin($user);

    $this->drupalGet("/admin/content/oe_organisation/add/oe_stakeholder");

    $this->getSession()->getPage()->fillField('Name', "My stakeholder");
    $this->getSession()->getPage()->fillField('Acronym', "My Acronym");
    $this->getSession()->getPage()->fillField('Street address', "Kossuth u. 120.");
    $this->getSession()->getPage()->fillField('Country', "HU");
    $this->getSession()->getPage()->fillField('City', "Budapest");
    $this->getSession()->getPage()->fillField('Company', "Best company");
    $this->getSession()->getPage()->fillField('Website', "https://test.com");
    $this->getSession()->getPage()->fillField('Contact page URL', "https://test2.com");
    $this->getSession()->getPage()->pressButton('Save');

    $this->assertSession()->pageTextContains("My stakeholder");
    $this->assertSession()->pageTextContains("My Acronym");
    $this->assertSession()->pageTextContains("My Acronym");
    $this->assertSession()->pageTextContains("Kossuth u. 120.");
    $this->assertSession()->pageTextContains("Hungary");
    $this->assertSession()->pageTextContains("Budapest");
    $this->assertSession()->pageTextContains("Best company");
    $this->assertSession()->pageTextContains("https://test.com");
    $this->assertSession()->pageTextContains("https://test2.com");

  }

}
