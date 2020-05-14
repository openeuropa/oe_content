<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_featured_media_field\FunctionalJavascript;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\media\Entity\Media;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the output of "oe_featured_media_widget" widget.
 *
 * @group oe_content_featured_media_field
 */
class FeaturedMediaFieldWidgetTest extends WebDriverTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * A field storage to use in this test class.
   *
   * @var \Drupal\field\Entity\FieldStorageConfig
   */
  protected $fieldStorage;

  /**
   * The field used in this test class.
   *
   * @var \Drupal\field\Entity\FieldConfig
   */
  protected $field;

  /**
   * The media entity used in this test class.
   *
   * @var \Drupal\media\Entity\Media
   */
  protected $mediaEntity;

  /**
   * The display options to use for the widget.
   *
   * @var array
   */
  protected $displayOptions = [
    'type' => 'oe_featured_media_widget',
    'settings' => [
      'match_operator' => 'CONTAINS',
      'match_limit' => 10,
      'size' => 60,
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'field_ui',
    'media',
    'oe_media',
    'oe_content_featured_media_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createContentType(['type' => 'page']);

    $user = $this->drupalCreateUser([
      'access content',
      'create page content',
    ]);
    $this->drupalLogin($user);

    $this->fieldStorage = FieldStorageConfig::create([
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'type' => 'oe_featured_media',
      'cardinality' => 1,
      'entity_types' => ['node'],
    ]);
    $this->fieldStorage->save();

    $this->field = FieldConfig::create([
      'label' => 'Featured media field',
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'bundle' => 'page',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
          ],
        ],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => '0',
      ],
      'required' => FALSE,
    ]);
    $this->field->save();

    // Create an image file.
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create(['uri' => 'public://example.jpg']);
    $image->save();

    // Create a media entity of image media type.
    $media_type = $this->container->get('entity_type.manager')->getStorage('media_type')->load('image');
    $this->mediaEntity = Media::create([
      'bundle' => $media_type->id(),
      'name' => 'Test image',
      'field_media_image' => [
        [
          'target_id' => $image->id(),
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $this->mediaEntity->save();

    // Prepare the default view display for rendering.
    $display = \Drupal::service('entity_display.repository')
      ->getFormDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle())
      ->setComponent($this->fieldStorage->getName(), $this->displayOptions);
    $display->save();
  }

  /**
   * Tests the featured media widget.
   */
  public function testFeaturedMediaWidget(): void {
    // Visit the node add page.
    $this->drupalGet('node/add/page');
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();

    // Assert that the fields and labels are present on the page.
    $assert_session->pageTextContains('Featured media');
    $assert_session->fieldExists('Media item');
    $assert_session->fieldExists('Caption');

    // Assert the help texts without media overview permission.
    $assert_session->pageTextContains('Type part of the media name.');
    $assert_session->pageTextNotContains('See the media list (opens a new window) to help locate media.');
    $assert_session->pageTextContains('Allowed media types: Image');

    // Login with a user with extended permissions.
    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'access media overview',
      'create image media',
      'create page content',
    ]));
    $this->drupalGet('node/add/page');

    // Assert the help texts with media overview permission.
    $assert_session->pageTextContains('Type part of the media name.');
    $assert_session->pageTextContains('See the media list (opens a new window) to help locate media.');
    $assert_session->pageTextContains('Allowed media types: Image');

    // Test the autocomplete functionality returns the image media.
    $this->doAutocomplete($this->fieldStorage->getName());

    $results = $page->findAll('css', '.ui-autocomplete li');

    $this->assertCount(1, $results);
    $assert_session->pageTextContains('Test image');
  }

  /**
   * Executes an autocomplete on a given field and waits for it to finish.
   *
   * @param string $field_name
   *   The field name.
   */
  protected function doAutocomplete(string $field_name): void {
    $autocomplete_field = $this->getSession()->getPage()->findField($field_name . '[0][featured_media][target_id]');
    $autocomplete_field->setValue('Test');
    $this->getSession()->getDriver()->keyDown($autocomplete_field->getXpath(), ' ');
    $this->assertSession()->waitOnAutocomplete();
  }

}
