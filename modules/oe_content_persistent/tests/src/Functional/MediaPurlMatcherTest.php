<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Functional;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\linkit\Tests\ProfileCreationTrait;

/**
 * Tests the media PURL matcher.
 */
class MediaPurlMatcherTest extends WebDriverTestBase {

  use ProfileCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'linkit',
    'media',
    'oe_content_persistent',
  ];

  /**
   * Test adding the configurable media matcher to a profile.
   */
  public function testAddMediaMatcher(): void {
    $user = $this->drupalCreateUser(['administer linkit profiles']);
    $this->drupalLogin($user);
    $profile = $this->createProfile();

    $this->drupalGet('/admin/config/content/linkit/manage/' . $profile->id() . '/matchers/add');

    // Create media matcher.
    $edit = [];
    $edit['plugin'] = 'entity:media';
    $this->submitForm($edit, 'Save and continue');

    $page = $this->getSession()->getPage();
    // Assert we are in the media matcher configuration page.
    $this->assertSession()->pageTextContains('Edit Media matcher');
    // Assert our custom fields are present.
    $this->assertSession()->fieldExists('include_unpublished');
    $this->assertSession()->fieldExists('thumbnail[show_thumbnail]');
    // The thumbnail style field should be hidden.
    $thumbnail_style_field = $page->findField('thumbnail[thumbnail_image_style]');
    $this->assertFalse($thumbnail_style_field->isVisible());
    // Check the custom fields and assert the thumbnail field is now visible.
    $page->checkField('include_unpublished');
    $page->checkField('thumbnail[show_thumbnail]');
    $thumbnail_style_field = $page->findField('thumbnail[thumbnail_image_style]');
    $this->assertTrue($thumbnail_style_field->isVisible());
    // Select an image style and save the changes.
    $page->selectFieldOption('thumbnail[thumbnail_image_style]', 'linkit_result_thumbnail');
    $page->pressButton('Save changes');
    $this->assertSession()->pageTextContains('Saved Media configuration.');
    // Assert values where saved.
    $this->assertSession()->pageTextContains('Show image thumbnail: Yes');
    $this->assertSession()->pageTextContains('Thumbnail style: Linkit result thumbnail');
  }

}
