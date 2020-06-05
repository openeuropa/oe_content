<?php

declare(strict_types = 1);

namespace Drupal\FunctionalTests\Entity;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests timeline widget field with constraint.
 */
class TimelineFieldValidationTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'oe_content_timeline_test_constraint',
    'oe_content_timeline_field',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->entityTypeManager->getStorage('node_type')->create([
      'name' => 'Page',
      'type' => 'page',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'timeline',
      'entity_type' => 'node',
      'type' => 'timeline_field',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'timeline',
      'bundle' => 'page',
    ])->save();

    $entity_form_display = EntityFormDisplay::collectRenderDisplay(Node::create(['type' => 'page']), 'default');
    $entity_form_display->setComponent('timeline', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'timeline_widget',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();
  }

  /**
   * Tests timeline field widgets errors.
   */
  public function testTimelineWidgetValidation() {
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    $values = [
      'title[0][value]' => 'My page',
      'timeline[0][body][value]' => 'Body',
    ];
    $this->drupalPostForm('/node/add/page', $values, 'Save');
    $this->assertSession()->pageTextContains('Label and Title fields cannot be empty when Content is specified.');
    $this->assertSession()->elementAttributeContains('css', '#edit-timeline-0-label', 'class', 'form-text error');
    $this->assertSession()->elementAttributeContains('css', '#edit-timeline-0-title', 'class', 'form-text error');
  }

}
