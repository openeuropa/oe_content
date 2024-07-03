<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_featured_media_field\FunctionalJavascript;

/**
 * Tests the output of "oe_featured_media_autocomplete" widget.
 *
 * @group oe_content_featured_media_field
 */
class FeaturedMediaFieldAutocompleteWidgetTest extends FeaturedMediaFieldWidgetTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

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
      ->getFormDisplay('node', 'page')
      ->setComponent('featured_media_field', $form_display_options);
    $display->save();

    // Prepare the default view display for rendering.
    $display = \Drupal::service('entity_display.repository')
      ->getViewDisplay('node', 'page')
      ->setComponent('featured_media_field', $view_display_options);

    $display->save();
  }

  /**
   * Tests the featured media widget.
   */
  public function testFeaturedMediaWidget(): void {
    // Login with a user with minimum permissions.
    $user = $this->drupalCreateUser([
      'access content',
      'create page content',
    ]);
    $this->drupalLogin($user);

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
    $assert_session->pageTextContains('Start typing the name of the Media.');
    $assert_session->pageTextContains('You can manage all the media items on this page.');
    $assert_session->pageTextContains('Allowed media types: Image');

    // Test that the Media item field turns required once the Caption is filled.
    $this->assertFalse($assert_session->fieldExists('Media item')->hasAttribute('required'));
    $page->fillField('Caption', 'Caption text');
    $this->assertTrue($assert_session->fieldExists('Media item')->hasAttribute('required'));

    // Test the autocomplete functionality returns the created media items.
    $this->doFeaturedMediaFieldAutocomplete('featured_media_field', 'Image');
    $results = $page->findAll('css', '.ui-autocomplete li');
    $this->assertCount(2, $results);
    $assert_session->pageTextContains('Image 1');
    $assert_session->pageTextContains('Image 2');

    // Assign the image media and save the node.
    $page->fillField('Media item', 'Image 1');
    $page->fillField('Title', 'My test node');
    $page->pressButton('Save');

    // Assert the label and values are visible on the node page.
    $assert_session->pageTextContains('Featured media');
    $assert_session->pageTextContains('Image 1');
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
  protected function doFeaturedMediaFieldAutocomplete(string $field_name, string $value): void {
    $autocomplete_field = $this->getSession()->getPage()->findField($field_name . '[0][featured_media][target_id]');
    $autocomplete_field->setValue($value);
    $this->getSession()->getDriver()->keyDown($autocomplete_field->getXpath(), ' ');
    $this->assertSession()->waitOnAutocomplete();
  }

}
