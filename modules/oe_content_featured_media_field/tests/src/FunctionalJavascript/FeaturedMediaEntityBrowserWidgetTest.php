<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_featured_media_field\FunctionalJavascript;

use Behat\Mink\Exception\ExpectationException;
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

    // Create 2 image media entities.
    $media_entity = Media::create([
      'bundle' => 'image',
      'name' => 'Image 1',
      'field_media_image' => [
        [
          'target_id' => $image->id(),
          'alt' => 'default alt',
          'title' => 'default title',
        ],
      ],
    ]);
    $media_entity->save();
    $media_entity = Media::create([
      'bundle' => 'image',
      'name' => 'Image 2',
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
      'cardinality' => -1,
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

    // Assert that all the entity browser elements are displayed.
    $this->assertSession()->buttonExists('Select images');
    $this->assertSession()->fieldExists('Caption');
    $this->assertSession()->pageTextContains('The caption that goes with the referenced media.');
    $this->assertSession()->buttonExists('Add another item');

    // Select one media image from the entity browser.
    $this->getSession()->getPage()->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:1]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert the image was selected and the widget shows the proper buttons.
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertSession()->buttonNotExists('Select images');
    $this->assertSession()->buttonExists('Remove');

    // Add the other media image item.
    $this->getSession()->getPage()->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:2]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Image 2');
    $this->assertSession()->buttonNotExists('Select images');

    // Check that 'Image 1' media item is placed before 'Image 2'.
    $this->assertOrderInPage(['Image 1', 'Image 2']);

    // Fill in the other fields and save the node.
    $this->getSession()->getPage()->fillField('featured_media_field[0][caption]', 'Image 1 caption');
    $this->getSession()->getPage()->fillField('featured_media_field[1][caption]', 'Image 2 caption');
    $this->getSession()->getPage()->fillField('Title', 'Test entity browser widget');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('page Test entity browser widget has been created.');
    $node = $this->drupalGetNodeByTitle('Test entity browser widget');
    $expected_values = [
      '0' => [
        'target_id' => '1',
        'caption' => 'Image 1 caption',
      ],
      '1' => [
        'target_id' => '2',
        'caption' => 'Image 2 caption',
      ],
    ];
    $actual_values = $node->get('featured_media_field')->getValue();
    $this->assertEquals($expected_values, $actual_values);
    // Assert the values on the page.
    $this->assertSession()->pageTextContains('Featured media field');
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertSession()->pageTextContains('Image 2');
    $this->assertSession()->pageTextContains('Image 1 caption');
    $this->assertSession()->pageTextContains('Image 2 caption');
    // Assert items order is the same as it was in the form.
    $this->assertOrderInPage(['Image 1', 'Image 2']);

    // Edit the node to reorder field items.
    $this->drupalGet('node/1/edit');
    $handle = $this->getSession()->getPage()->find('css', 'table#featured-media-field-values > tbody > tr:nth-child(1) a.tabledrag-handle');
    $target = $this->getSession()->getPage()->find('css', 'table#featured-media-field-values > tbody > tr:nth-child(2) a.tabledrag-handle');
    $handle->dragTo($target);
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Check that 'Image 1' media item is placed after 'Image 2'.
    $this->assertOrderInPage(['Image 2', 'Image 1']);
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('page Test entity browser widget has been updated.');
    $node = $this->drupalGetNodeByTitle('Test entity browser widget', TRUE);
    $expected_values = [
      '0' => [
        'target_id' => '2',
        'caption' => 'Image 2 caption',
      ],
      '1' => [
        'target_id' => '1',
        'caption' => 'Image 1 caption',
      ],
    ];
    $actual_values = $node->get('featured_media_field')->getValue();
    $this->assertEquals($expected_values, $actual_values);

    // Assert items order was saved.
    $this->assertOrderInPage(['Image 2', 'Image 1']);
    // Assert items on the page.
    $this->assertSession()->pageTextContains('Featured media field');
    $this->assertSession()->pageTextContains('Image 2');
    $this->assertSession()->pageTextContains('Image 2 caption');
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertSession()->pageTextContains('Image 1 caption');

    // Edit the node to remove the current first item in the form (Image 2).
    $this->drupalGet('node/1/edit');
    $this->getSession()->getPage()->pressButton('edit-featured-media-field-0-current-items-0-remove-button');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Assert the image was removed from the field.
    $this->assertSession()->pageTextNotContains('Image 2');
    $this->assertSession()->buttonExists('Select images');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('page Test entity browser widget has been updated.');
    $node = $this->drupalGetNodeByTitle('Test entity browser widget', TRUE);
    $expected_values = [
      '0' => [
        'target_id' => '1',
        'caption' => 'Image 1 caption',
      ],
    ];
    $actual_values = $node->get('featured_media_field')->getValue();
    $this->assertEquals($expected_values, $actual_values);
    $this->assertSession()->pageTextContains('Featured media field');
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertSession()->pageTextContains('Image 1 caption');

    // The removed item should not be displayed on the page anymore.
    $this->assertSession()->pageTextNotContains('Image 2');
    $this->assertSession()->pageTextNotContains('Image 2 caption');
  }

  /**
   * Asserts strings are placed in markup in the given order.
   */
  protected function assertOrderInPage(array $expected_strings): void {
    $session = $this->getSession();
    $text = $session->getPage()->getHtml();
    $actual_strings = [];
    foreach ($expected_strings as $string) {
      if (($pos = strpos($text, $string)) === FALSE) {
        throw new ExpectationException("Cannot find '$string' in the page", $session->getDriver());
      }
      $actual_strings[$pos] = $string;
    }
    ksort($actual_strings);
    $this->assertSame($expected_strings, array_values($actual_strings));
  }

}
