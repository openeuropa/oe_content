<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_description_list_field\Functional;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Description list field widget.
 */
class DescriptionListFieldWidgetTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'node',
    'system',
    'field',
    'text',
    'oe_content_description_list_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->container->get('entity_type.manager')->getStorage('node_type')->create([
      'name' => 'Page',
      'type' => 'page',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'description_list',
      'entity_type' => 'node',
      'type' => 'description_list_field',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => 'description_list',
      'bundle' => 'page',
    ])->save();

    $entity_form_display = EntityFormDisplay::collectRenderDisplay(Node::create(['type' => 'page']), 'default');
    $entity_form_display->setComponent('description_list', [
      'weight' => 1,
      'region' => 'content',
      'type' => 'description_list_widget',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    $entity_form_display->save();
  }

  /**
   * Tests the Description list field widget.
   */
  public function testDescriptionListFieldWidget(): void {
    $user = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($user);

    $this->drupalGet('/node/add/page');

    $values = [
      'title[0][value]' => 'My page',
      'description_list[0][term]' => 'Term 1',
      'description_list[0][description][value]' => 'Description 1',
    ];

    $this->drupalPostForm('/node/add/page', $values, 'Save');
    $this->assertSession()->pageTextContains('Page My page has been created');

    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->container->get('entity_type.manager')->getStorage('node')->load(1);
    $expected_values = [
      'term' => 'Term 1',
      'description' => 'Description 1',
      'format' => 'plain_text',
    ];
    $this->assertEquals($expected_values, $node->get('description_list')->first()->getValue());
  }

}
