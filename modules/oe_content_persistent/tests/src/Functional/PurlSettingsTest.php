<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_persistent\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests the module configuration.
 *
 * @group oe_content
 */
class PurlSettingsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'path',
    'node',
    'user',
    'system',
    'oe_content_persistent',
  ];

  /**
   * Tests the module settings form works as intended.
   */
  public function testPurlSettingsForm(): void {
    // Log in with a user that can access the form.
    $user = $this->createUser(['configure purl settings']);
    $this->drupalLogin($user);
    $this->drupalGet('/admin/config/purl/settings');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('PURL settings');

    // Assert the default values.
    $this->assertSession()->fieldValueEquals('Inter institutional base url', 'https://data.ec.europa.eu/ewp/');
    $this->assertSession()->checkboxChecked('supported_entity_types[node]');
    $this->assertSession()->checkboxNotChecked('supported_entity_types[user]');
    $this->assertSession()->checkboxNotChecked('supported_entity_types[path_alias]');

    // Try to save the form without selecting any entity type.
    $this->getSession()->getPage()->uncheckField('supported_entity_types[node]');
    $this->getSession()->getPage()->pressButton('Save configuration');

    // Assert that the form triggered an error.
    $this->assertSession()->pageTextContains('Supported entity types field is required.');

    // Alter values and save the form.
    $this->getSession()->getPage()->checkField('supported_entity_types[user]');
    $this->getSession()->getPage()->fillField('Inter institutional base url', 'https://custom-site.com');
    $this->getSession()->getPage()->pressButton('Save configuration');
    $this->assertSession()->pageTextContains('The configuration options have been saved.');

    // Assert the values where saved.
    $this->assertSession()->fieldValueEquals('Inter institutional base url', 'https://custom-site.com');
    $this->assertSession()->checkboxNotChecked('supported_entity_types[node]');
    $this->assertSession()->checkboxNotChecked('supported_entity_types[path_alias]');
    $this->assertSession()->checkboxChecked('supported_entity_types[user]');
    $config = \Drupal::config('oe_content_persistent.settings');
    $this->assertEquals('https://custom-site.com', $config->get('base_url'));
    $expected_supported_entity_types = [
      'user' => 'user',
    ];
    $this->assertEquals($expected_supported_entity_types, $config->get('supported_entity_types'));
  }

}
