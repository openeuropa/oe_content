<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\FunctionalJavascript;

use Drupal\media\Entity\Media;
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
    // Create dummy media entity image.
    $file_data = file_get_contents('fixtures/bird.jpg');
    $file = file_save_data($file_data);
    $media = Media::create([
      'bundle' => 'image',
      'uid' => 1,
      'oe_media_image' => [
        'target_id' => (int) $file->id(),
        'alt' => 'A nice bird',
        'title' => 'A nice bird',
      ],
    ]);
    $media->setName('Bird')->setPublished()->save();

    $user = $this->drupalCreateUser([
      'manage corporate content entities',
      'access administration pages',
      'create oe_organisation oe_stakeholder corporate entity',
      'edit oe_organisation oe_stakeholder corporate entity',
      'access oe_organisation canonical page',
    ]);

    $this->drupalLogin($user);
    $this->drupalGet("/admin/content/oe_organisation/add/oe_stakeholder");

    $fields = [
      'Name' => 'My stakeholder',
      'Acronym' => 'My Acronym',
      'oe_logo[0][target_id]' => 'Bird',
      'Country' => 'HU',
      'Street address' => 'My street 20.',
      'City' => 'Budapest',
      'Postal code' => '1171',
      'Website' => 'https://test.com',
      'Contact page URL' => 'https://test2.com',
    ];

    foreach ($fields as $key => $value) {
      $this->getSession()->getPage()->fillField($key, $value);
    }

    $this->getSession()->getPage()->pressButton('Save');

    $this->drupalGet("/admin/content/oe_organisation/1/edit");
    foreach ($fields as $key => $value) {
      if ($key == 'Name') {
        $this->assertSession()->pageTextContains("Created the My stakeholder.");
      }
      elseif ($key == 'Country') {
        $this->assertOptionSelected('Country', $value);
      }
      else {
        $field_value = $this->assertSession()->fieldExists($key)->getValue();
        $this->assertTrue($field_value == $value);
      }
    }
  }

}
