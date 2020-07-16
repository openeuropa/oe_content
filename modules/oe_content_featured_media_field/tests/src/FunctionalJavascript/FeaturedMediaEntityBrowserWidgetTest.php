<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_featured_media_field\FunctionalJavascript;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the output of "oe_featured_media_entity_browser" widget.
 *
 * @group oe_content_featured_media_field
 */
class FeaturedMediaEntityBrowserWidgetTest extends FeaturedMediaFieldWidgetTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'views',
    'block',
    'views_ui',
    'system',
    'oe_content_featured_media_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $view_display_options = [
      'type' => 'oe_featured_media_label',
      'label' => 'above',
      'settings' => [
        'link' => TRUE,
      ],
    ];

    FieldStorageConfig::load('node.featured_media_field')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->save();

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

    // Assert validation of caption without Media.
    $this->getSession()->getPage()->fillField('Title', 'Test entity browser widget');
    $this->getSession()->getPage()->fillField('featured_media_field[0][caption]', 'Invalid caption');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Please either remove the caption or select a Media entity');

    $this->drupalGet('node/add/page');

    // Select the first media image from the entity browser.
    $this->getSession()->getPage()->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:1]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert the image was selected and the widget shows the proper buttons.
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertSession()->buttonNotExists('Select images');
    $this->assertMediaSelectionHasRemoveButton('Image 1');

    // Add the second media image item.
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
    $this->assertMediaSelectionHasRemoveButton('Image 2');

    // Check that 'Image 1' media item is placed before 'Image 2'.
    $this->assertOrderInPage(['Image 1', 'Image 2']);

    // Fill in the other fields and save the node.
    $this->getSession()->getPage()->fillField('featured_media_field[0][caption]', 'Image 1 caption');
    $this->getSession()->getPage()->fillField('featured_media_field[1][caption]', 'Image 2 caption');
    $this->getSession()->getPage()->fillField('Title', 'Test entity browser widget');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('Test entity browser widget has been created.');
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

    // Assert the remaining caption should not remain.
    $this->assertSession()->pageTextContains('Please either remove the caption or select a Media entity');

    // Remove the caption.
    $this->getSession()->getPage()->fillField('featured_media_field[0][caption]', '');
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
   * Asserts that the media selection has the Remove button at a given delta.
   *
   * @param string $name
   *   The name of the media item.
   */
  protected function assertMediaSelectionHasRemoveButton(string $name): void {
    $xpath = '//table//tbody//tr[td//text()[contains(., "' . $name . '")]]';
    $container = $this->getSession()->getPage()->find('xpath', $xpath);
    if (!$container) {
      $this->fail(sprintf('The media item %s was not found', $name));
    }

    $this->assertSession()->buttonExists('Remove', $container);
  }

  /**
   * Asserts strings are placed in markup in the given order.
   *
   * @param array $expected_strings
   *   The strings in the expected order.
   */
  protected function assertOrderInPage(array $expected_strings): void {
    $session = $this->getSession();
    $text = $session->getPage()->getHtml();
    $actual_strings = [];
    foreach ($expected_strings as $string) {
      $this->assertSession()->pageTextContains($string);
      $actual_strings[strpos($text, $string)] = $string;
    }
    ksort($actual_strings);
    $this->assertSame($expected_strings, array_values($actual_strings));
  }

}
