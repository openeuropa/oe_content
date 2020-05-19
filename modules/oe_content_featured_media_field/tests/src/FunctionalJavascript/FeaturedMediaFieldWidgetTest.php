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
class FeaturedMediaFieldWidgetTest extends WebDriverTestBase {

  use ContentTypeCreationTrait;
  use NodeCreationTrait;
  use MediaTypeCreationTrait;

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

    // Create an image file.
    \Drupal::service('file_system')->copy($this->root . '/core/misc/druplicon.png', 'public://example.jpg');
    $image = File::create(['uri' => 'public://example.jpg']);
    $image->save();

    // Create a media entity of image media type.
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

    // Create another media entity.
    $media_entity = Media::create([
      'bundle' => 'document',
      'name' => 'Test media',
    ]);
    $media_entity->save();

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
            'document' => 'document',
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

    // Setup the display options for form and view.
    $form_display_options = [
      'type' => 'oe_featured_media_autocomplete',
      'settings' => [
        'match_operator' => 'CONTAINS',
        'match_limit' => 10,
        'size' => 60,
      ],
    ];
    $view_display_options = [
      'type' => 'oe_featured_media_label',
      'label' => 'above',
      'settings' => [
        'link' => TRUE,
      ],
    ];

    // Prepare the default form display for rendering.
    $display = \Drupal::service('entity_display.repository')
      ->getFormDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle())
      ->setComponent($this->fieldStorage->getName(), $form_display_options);
    $display->save();

    // Prepare the default view display for rendering.
    $display = \Drupal::service('entity_display.repository')
      ->getViewDisplay($this->field->getTargetEntityTypeId(), $this->field->getTargetBundle())
      ->setComponent($this->fieldStorage->getName(), $view_display_options);
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
    $assert_session->pageTextContains('Start typing the name of the Media.');
    $assert_session->pageTextNotContains('You can manage all the media items on this page.');
    $assert_session->pageTextContains('Allowed media types: Image, Document');

    // Login with a user with extended permissions.
    $this->drupalLogin($this->drupalCreateUser([
      'access content',
      'access media overview',
      'create image media',
      'create page content',
    ]));
    $this->drupalGet('node/add/page');

    // Assert the help texts with media overview permission.
    $assert_session->pageTextContains('Start typing the name of the Media.');
    $assert_session->pageTextContains('You can manage all the media items on this page.');
    $assert_session->pageTextContains('Allowed media types: Image, Document');

    // Test that the Media item field turns required once the Caption is filled.
    $this->assertFalse($page->findField('Media item')->hasAttribute('required'));
    $page->fillField('Caption', 'Caption text');
    $this->assertTrue($page->findField('Media item')->hasAttribute('required'));

    // Test the autocomplete functionality returns the created media items.
    $this->doAutocomplete($this->fieldStorage->getName(), 'Test');
    $results = $page->findAll('css', '.ui-autocomplete li');
    $this->assertCount(2, $results);
    $assert_session->pageTextContains('Test image');
    $assert_session->pageTextContains('Test media');

    // Assign the image media and save the node.
    $page->fillField('Media item', 'Test image');
    $page->fillField('Title', 'My test node');
    $page->pressButton('Save');

    // Assert the label and values are visible on the node page.
    $assert_session->pageTextContains('Featured media');
    $assert_session->pageTextContains('Test image');
    $assert_session->pageTextContains('Caption text');
  }

  /**
   * Executes an autocomplete on a given field and waits for it to finish.
   *
   * @param string $field_name
   *   The field name.
   * @param string $value
   *   The value to look for.
   */
  protected function doAutocomplete(string $field_name, string $value): void {
    $autocomplete_field = $this->getSession()->getPage()->findField($field_name . '[0][featured_media][target_id]');
    $autocomplete_field->setValue($value);
    $this->getSession()->getDriver()->keyDown($autocomplete_field->getXpath(), ' ');
    $this->assertSession()->waitOnAutocomplete();
  }

}
