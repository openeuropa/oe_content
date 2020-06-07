<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_featured_media_field\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\media\Entity\Media;
use Drupal\Tests\media\Traits\MediaTypeCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the output of "oe_featured_media_widget" widget.
 *
 * @group oe_content_featured_media_field
 */
class FeaturedMediaEntityBrowserWidgetTest extends WebDriverTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;
  use MediaTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field',
    'field_ui',
    'media',
    'media_test_source',
    'oe_media',
    'oe_content_featured_media_field',
    'views',
    'block',
    'views_ui',
    'system',
    'oe_content_featured_media_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createContentType(['type' => 'page']);

    // Create an image file.
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create(['uri' => 'public://example.jpg']);
    $image->save();

    // Create image media entity.
    $media_entity = Media::create([
      'bundle' => 'image',
      'name' => 'Test image',
      'field_media_image' => [
        [
          'target_id' => $image->id(),
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $media_entity->save();

    FieldStorageConfig::create([
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'type' => 'oe_featured_media',
      'cardinality' => 1,
      'settings' => [
        'target_type' => 'media',
      ],
    ])->save();

    FieldConfig::create([
      'label' => 'Featured media field',
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'bundle' => 'page',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
            'av_portal_photo' => 'av_portal_photo',
          ],
        ],
      ],
    ])->save();

    $view_display_options = [
      'type' => 'oe_featured_media_label',
      'label' => 'above',
      'settings' => [
        'link' => TRUE,
      ],
    ];

    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $this->container->get('entity_type.manager')
      ->getStorage('entity_form_display')
      ->load('node.page.default');

    $form_display->setComponent('featured_media_field', [
      'type' => 'oe_featured_media_entity_browser',
      'settings' => [
        'entity_browser' => 'test_images',
        'field_widget_display' => 'label',
        'open' => TRUE,
      ],
    ])->save();

    // Prepare the default view display for rendering.
    $display = \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', 'page')
      ->setComponent('featured_media_field', $view_display_options);
    $display->save();

    $this->drupalLogin($this->drupalCreateUser([], '', TRUE));
  }

  /**
   * Tests the featured media entity browser widget.
   */
  public function testFeaturedMediaEntityBrowserWidget(): void {
    $this->drupalGet('node/add/page');

    // Assert the help texts, the caption field and entity browser button exist.
    $this->assertSession()->buttonExists('Select images');
    $this->assertSession()->pageTextContains('You can manage all the media items on this page.');
    $this->assertSession()->pageTextContains('Allowed media types: Image');
    $this->assertSession()->pageTextContains('You can select one media item.');
    $this->assertSession()->fieldExists('Caption');
    $this->assertSession()->pageTextContains('The caption that goes with the referenced media.');

    // Select the media image from the entity browser.
    $this->getSession()->getPage()->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:1]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert the image was selected and the widget shows the proper buttons.
    $this->assertSession()->pageTextContains('Test image');
    $this->assertSession()->buttonNotExists('Select images');
    $this->assertSession()->buttonExists('Remove');
    $this->assertSession()->buttonExists('Edit');

    // Fill in the other fields and save the node.
    $this->getSession()->getPage()->fillField('Caption', 'Image caption');
    $this->getSession()->getPage()->fillField('Title', 'Test entity browser widget');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert that the values were saved.
    $this->assertSession()->pageTextContains('Featured media field');
    $this->assertSession()->pageTextContains('Test image');
    $this->assertSession()->pageTextContains('Image caption');

    // Edit the node to remove the media.
    $this->drupalGet('node/1/edit');
    $this->getSession()->getPage()->pressButton('Remove');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Assert the image was removed from the field.
    $this->assertSession()->pageTextNotContains('Test image');
    $this->assertSession()->buttonExists('Select images');
  }

}
