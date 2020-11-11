<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_featured_media_field\FunctionalJavascript;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;

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
    'entity_reference_revisions',
    'inline_entity_form',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Add an entity reference field with unlimited cardinality.
    FieldStorageConfig::create([
      'field_name' => 'news_reference_field',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'settings' => [
        'target_type' => 'node',
      ],
    ])->save();

    FieldConfig::create([
      'label' => 'News reference field',
      'field_name' => 'news_reference_field',
      'entity_type' => 'node',
      'bundle' => 'page',
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => [
            'oe_news' => 'oe_news',
          ],
        ],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => '0',
      ],
      'required' => FALSE,
    ])->save();

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
    $form_display = EntityFormDisplay::load('node.page.default');

    $form_display->setComponent('featured_media_field', [
      'type' => 'oe_featured_media_entity_browser',
      'settings' => [
        'entity_browser' => 'test_images',
        'field_widget_display' => 'label',
        'open' => TRUE,
      ],
    ]);
    $form_display->setComponent('news_reference_field', [
      'type' => 'entity_reference_autocomplete',
      'settings' => [
        'match_operator' => 'CONTAINS',
        'size' => 60,
        'match_limit' => 10,
      ],
    ])->save();

    // Prepare the default view display for rendering.
    $display = \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', 'page')
      ->setComponent('featured_media_field', $view_display_options);
    $display->save();

    Node::create([
      'type' => 'oe_news',
      'title' => 'News node',
    ])->save();

    $this->drupalLogin($this->drupalCreateUser([], '', TRUE));
  }

  /**
   * Tests the featured media entity browser widget.
   */
  public function testFeaturedMediaEntityBrowserWidget(): void {
    $this->drupalGet('node/add/page');

    // Add another item in a different entity reference field to ensure AJAX
    // is not broken due to our widget.
    $this->getSession()->getPage()->fillField('Title', 'Node with featured media');
    $this->getSession()->getPage()->fillField('News reference field (value 1)', 'News node');
    $this->getSession()->getPage()->pressButton('Save');

    $node = $this->drupalGetNodeByTitle('Node with featured media');
    $this->drupalGet($node->toUrl('edit-form'));
    $news_reference_field = $this->getSession()->getPage()->find('css', 'div#news-reference-field-add-more-wrapper');
    $news_reference_field->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Assert the item was correctly added.
    $this->assertSession()->pageTextContains('News reference field (value 2)');

    // Assert that all the entity browser elements are displayed.
    $featured_media_field_wrapper = $this->xpath('//div[@data-drupal-selector=\'edit-featured-media-field\']');
    $this->assertCount(1, $featured_media_field_wrapper);
    $featured_media_field = reset($featured_media_field_wrapper);
    $this->assertTrue($featured_media_field->hasButton('Select images'));
    $this->assertTrue($featured_media_field->hasField('Caption'));
    $this->assertSession()->pageTextContains('The caption that goes with the referenced media.');
    $this->assertTrue($featured_media_field->hasButton('Add another item'));

    // Assert validation of caption without Media.
    $this->getSession()->getPage()->fillField('featured_media_field[0][caption]', 'Invalid caption');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Please either remove the caption or select a Media entity');

    // Start over and use the entity browser.
    $this->drupalGet($node->toUrl('edit-form'));

    // Select the first media image from the entity browser.
    $featured_media_field->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:1]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert the image was selected and the widget shows the proper buttons.
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertFalse($featured_media_field->hasButton('Select images'));
    $this->assertMediaSelectionHasRemoveButton('Image 1', 'Featured media field');

    // Add the second media image item.
    $this->getSession()->getPage()->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Assert that the item was added to the featured media field.
    $this->assertSession()->pageTextContains('Featured media field (value 2)');
    $featured_media_field->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:2]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Image 2');
    $this->assertFalse($featured_media_field->hasButton('Select images'));
    $this->assertMediaSelectionHasRemoveButton('Image 2', 'Featured media field');

    // Check that 'Image 1' media item is placed before 'Image 2'.
    $this->assertOrderInPage(['Image 1', 'Image 2']);

    // Fill in the captions and save the node.
    $this->getSession()->getPage()->fillField('featured_media_field[0][caption]', 'Image 1 caption');
    $this->getSession()->getPage()->fillField('featured_media_field[1][caption]', 'Image 2 caption');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('Node with featured media has been updated.');
    $node = $this->drupalGetNodeByTitle('Node with featured media', TRUE);
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
    $this->drupalGet($node->toUrl('edit-form'));
    $handle = $this->getSession()->getPage()->find('css', 'table#featured-media-field-values > tbody > tr:nth-child(1) a.tabledrag-handle');
    $target = $this->getSession()->getPage()->find('css', 'table#featured-media-field-values > tbody > tr:nth-child(2) a.tabledrag-handle');
    $handle->dragTo($target);
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Check that 'Image 1' media item is placed after 'Image 2'.
    $this->assertOrderInPage(['Image 2', 'Image 1']);
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('page Node with featured media has been updated.');
    $node = $this->drupalGetNodeByTitle('Node with featured media', TRUE);
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
    $this->drupalGet($node->toUrl('edit-form'));
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
    $this->assertSession()->pageTextContains('page Node with featured media has been updated.');
    $node = $this->drupalGetNodeByTitle('Node with featured media', TRUE);
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
   * Tests the featured media entity browser widget inside IEF.
   */
  public function testFeaturedMediaEntityBrowserWidgetIef(): void {
    // Create a content type we can embed via IEF (article).
    $this->createContentType(['type' => 'article']);

    // Add the featured media field to the embedded content type.
    FieldConfig::create([
      'label' => 'Article Featured media field',
      'field_name' => 'featured_media_field',
      'entity_type' => 'node',
      'bundle' => 'article',
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
    ])->save();

    // Create the reference field to the embedded node on the main tested
    // content type (page).
    FieldStorageConfig::create([
      'field_name' => 'article_field',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => 1,
      'settings' => [
        'target_type' => 'node',
      ],
    ])->save();

    FieldConfig::create([
      'label' => 'Article IEF',
      'field_name' => 'article_field',
      'entity_type' => 'node',
      'bundle' => 'page',
      'settings' => [
        'handler' => 'default:node',
        'handler_settings' => [
          'target_bundles' => [
            'node_type' => 'article',
          ],
        ],
        'sort' => [
          'field' => '_none',
        ],
        'auto_create' => '0',
      ],
      'required' => FALSE,
    ])->save();

    // Configure the form display of the article reference field to use IEF.
    $form_display = EntityFormDisplay::load('node.page.default');
    $form_display->setComponent('article_field', [
      'type' => 'inline_entity_form_complex',
      'settings' => [
        'match_operator' => 'CONTAINS',
      ],
    ])->save();

    // Configure the embedded CT (article) form display to use the entity
    // browser featured media.
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = EntityFormDisplay::load('node.article.default');
    $form_display->setComponent('featured_media_field', [
      'type' => 'oe_featured_media_entity_browser',
      'settings' => [
        'entity_browser' => 'test_images',
        'field_widget_display' => 'label',
        'open' => TRUE,
      ],
    ])->save();

    // Configure the embedded CT (article) view display.
    $view_display_options = [
      'type' => 'oe_featured_media_label',
      'label' => 'above',
      'settings' => [
        'link' => TRUE,
      ],
    ];
    $display = \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', 'article')
      ->setComponent('featured_media_field', $view_display_options);
    $display->save();

    $this->drupalGet('node/add/page');
    $this->getSession()->getPage()->fillField('Title', 'Node with featured media');

    // Add image item in IEF.
    $this->getSession()->getPage()->pressButton('Add new node');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->fillField('article_field[form][0][title][0][value]', 'Article node with IEF');

    $featured_media_field_wrapper = $this->xpath('//div[@data-drupal-selector=\'edit-article-field-wrapper\']/div/fieldset/div/fieldset/div/div/div[@data-drupal-selector=\'edit-article-field-form-0-featured-media-field-wrapper\']');
    $this->assertCount(1, $featured_media_field_wrapper);
    $featured_media_field = reset($featured_media_field_wrapper);
    $this->assertTrue($featured_media_field->hasButton('Select images'));
    $this->assertTrue($featured_media_field->hasField('Caption'));
    $this->assertSession()->pageTextContains('The caption that goes with the referenced media.');
    $this->assertTrue($featured_media_field->hasButton('Add another item'));

    // Assert validation of caption without Media.
    $this->getSession()->getPage()->fillField('article_field[form][0][featured_media_field][0][caption]', 'Invalid caption');
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Please either remove the caption or select a Media entity');

    // Select the first media image from the entity browser.
    $featured_media_field->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:1]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert the image was selected and the widget shows the proper buttons.
    $this->assertSession()->pageTextContains('Image 1');
    $this->assertFalse($featured_media_field->hasButton('Select images'));
    $this->assertMediaSelectionHasRemoveButton('Image 1', 'Article Featured media field');

    // Add the second media image item.
    $featured_media_field->pressButton('Add another item');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Assert that the item was added to the featured media field.
    $this->assertSession()->pageTextContains('Featured media field (value 2)');
    $featured_media_field->pressButton('Select images');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->switchToIFrame('entity_browser_iframe_test_images');
    $this->getSession()->getPage()->checkField('entity_browser_select[media:2]');
    $this->getSession()->getPage()->pressButton('Select image');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertSession()->pageTextContains('Image 2');
    $this->assertFalse($featured_media_field->hasButton('Select images'));
    $this->assertMediaSelectionHasRemoveButton('Image 2', 'Article Featured media field');

    // Check that 'Image 1' media item is placed before 'Image 2'.
    $this->assertOrderInPage(['Image 1', 'Image 2']);

    // Fill in the captions and save the node.
    $this->getSession()->getPage()->fillField('article_field[form][0][featured_media_field][0][caption]', 'Image 1 caption');
    $this->getSession()->getPage()->fillField('article_field[form][0][featured_media_field][1][caption]', 'Image 2 caption');
    $this->getSession()->getPage()->pressButton('Create node');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->getSession()->getPage()->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->assertOrderInPage(['Image 1', 'Image 2']);
    $this->getSession()->getPage()->pressButton('Update node');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('Node with featured media has been created.');
    $node = $this->drupalGetNodeByTitle('Article node with IEF', TRUE);
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

    // Edit the node to remove the current first item in the form (Image 1).
    $node = $this->drupalGetNodeByTitle('Node with featured media', TRUE);
    $this->drupalGet($node->toUrl('edit-form'));
    // Open the IEF form of the embedded article.
    $this->getSession()->getPage()->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $remove = $this->getSession()->getPage()->find('css', 'input[data-drupal-selector="edit-article-field-form-inline-entity-form-entities-0-form-featured-media-field-0-current-items-0-remove-button"]');
    $remove->press();
    $this->assertSession()->assertWaitOnAjaxRequest();

    // Assert the image was removed from the field.
    $this->assertSession()->pageTextNotContains('Image 1');
    $this->assertSession()->elementExists('css', 'input[data-drupal-selector="edit-article-field-form-inline-entity-form-entities-0-form-featured-media-field-0-entity-browser-entity-browser-open-modal"]');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the remaining caption can't be saved when the image is removed.
    $this->assertSession()->pageTextContains('Please either remove the caption or select a Media entity');

    // Remove the caption.
    $this->getSession()->getPage()->fillField('article_field[form][inline_entity_form][entities][0][form][featured_media_field][0][caption]', '');
    $this->getSession()->getPage()->pressButton('Save');

    // Assert the values were saved correctly in the node.
    $this->assertSession()->pageTextContains('Node with featured media has been updated.');
    $node = $this->drupalGetNodeByTitle('Article node with IEF', TRUE);
    $expected_values = [
      '0' => [
        'target_id' => '2',
        'caption' => 'Image 2 caption',
      ],
    ];
    $actual_values = $node->get('featured_media_field')->getValue();
    $this->assertEquals($expected_values, $actual_values);

    // Edit the node to remove the remaining media (but leave the caption).
    $node = $this->drupalGetNodeByTitle('Node with featured media', TRUE);
    $this->drupalGet($node->toUrl('edit-form'));
    // Open the IEF form of the embedded article.
    $this->getSession()->getPage()->pressButton('Edit');
    $this->assertSession()->assertWaitOnAjaxRequest();

    $remove = $this->getSession()->getPage()->find('css', 'input[data-drupal-selector="edit-article-field-form-inline-entity-form-entities-0-form-featured-media-field-0-current-items-0-remove-button"]');
    $remove->press();
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->getPage()->pressButton('Save');
    $this->assertSession()->pageTextContains('Please either remove the caption or select a Media entity');
    // Assert that if the validation fails, the media selection remains empty.
    $this->assertSession()->pageTextNotContains('Image 2');
  }

  /**
   * Asserts that the media selection has the Remove button in a given field.
   *
   * @param string $media_name
   *   The name of the media item.
   * @param string $field_name
   *   The name of the field.
   */
  protected function assertMediaSelectionHasRemoveButton(string $media_name, string $field_name): void {
    $xpath = '//table[thead//tr[th//text()[contains(., "' . $field_name . '")]]][tbody//tr[td//text()[contains(., "' . $media_name . '")]]]';
    $container = $this->getSession()->getPage()->find('xpath', $xpath);
    if (!$container) {
      $this->fail(sprintf('The media item %s was not found', $media_name));
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
