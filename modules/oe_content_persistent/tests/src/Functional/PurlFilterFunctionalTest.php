<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Kernel;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests Persistent url filter in browser test.
 *
 * @group path
 */
class PurlFilterFunctionalTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'path',
    'node',
    'text',
    'user',
    'system',
    'language',
    'content_translation',
    'oe_content_persistent',
    'page_cache',
    'dynamic_page_cache',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $node_type = NodeType::create(['type' => 'page']);
    $node_type->save();
    node_add_body_field($node_type);

    ConfigurableLanguage::create(['id' => 'fr'])->save();

  }

  /**
   * Tests the path cache with Persistent Controller.
   */
  public function testPersistentUrlController(): void {

    $node1 = Node::create([
      'title' => $this->randomString(),
      'type' => 'page',
      'path' => ['alias' => '/' . $this->randomString()],
      'status' => TRUE,
    ]);
    $node1->save();

    $node2 = Node::create([
      'title' => $this->randomString(),
      'type' => 'page',
      'status' => TRUE,
    ]);
    $node2->save();

    $node2->body = '<a href="/content/' . $node1->uuid() . '">test</a>';
    $node2->save();

    $this->drupalGet('/node/' . $node2->id());

    $this->assertResponse(200);

    $this->assertSession()->linkExists('test');

  }

}
