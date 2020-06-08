<?php

declare(strict_types = 1);

namespace Drupal\FunctionalTests\Entity;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
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

    NodeType::create([
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

    $node = Node::create(['type' => 'page']);
    $entity_form_display = EntityFormDisplay::collectRenderDisplay($node, 'default');
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

    $state = \Drupal::state();
    $state->set('oe_content_timeline_test_constraint.error_paths', [0 => 0]);

    $values = [
      'title[0][value]' => 'My page',
      'timeline[0][body][value]' => 'Body text',
    ];
    $this->drupalPostForm('/node/add/page', $values, 'Save');
    $this->assertSession()->pageTextContains('Label and Title fields cannot be empty when Content is specified.');
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-label')->hasClass('error'));
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-title')->hasClass('error'));
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-body-value')->hasClass('error'));

    $state->set('oe_content_timeline_test_constraint.error_paths', [0 => '0.label']);
    $this->drupalPostForm('/node/add/page', $values, 'Save');
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-label')->hasClass('error'));
    $this->assertFalse($this->assertSession()->fieldExists('edit-timeline-0-title')->hasClass('error'));
    $this->assertFalse($this->assertSession()->fieldExists('edit-timeline-0-body-value')->hasClass('error'));

    $state->set('oe_content_timeline_test_constraint.error_paths', [0 => '0.nonexistant']);
    $this->drupalPostForm('/node/add/page', $values, 'Save');
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-label')->hasClass('error'));
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-title')->hasClass('error'));
    $this->assertTrue($this->assertSession()->fieldExists('edit-timeline-0-body-value')->hasClass('error'));
  }

}
