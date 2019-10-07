<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Functional;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the PersistentUrlController response.
 *
 * @group oe_content
 */
class PersistentUrlControllerTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'path',
    'node',
    'user',
    'system',
    'dynamic_page_cache',
    'page_cache',
    'oe_content_persistent',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $node_type = NodeType::create(['type' => 'page']);
    $node_type->save();

    $user = $this->drupalCreateUser(['access content']);
    $this->drupalLogin($user);
  }

  /**
   * Tests the response of the Persistent Controller.
   */
  public function testPersistentUrlController(): void {
    $node = Node::create([
      'title' => 'Testing create()',
      'type' => 'page',
      'path' => ['alias' => '/foo'],
      'status' => TRUE,
      'uid' => 0,
    ]);
    $node->save();

    $this->drupalGet('/content/' . $node->uuid());
    $this->assertResponse(200);
    $this->assertUrl('/foo');
    $this->assertText('Testing create()');

    $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($node->id());
    $node->path->alias = '/foo2';
    $node->save();

    // Ensure the cache is invalidated correctly.
    $this->drupalGet('/content/' . $node->uuid());
    $this->assertResponse(200);
    $this->assertUrl('/foo2');
    $this->assertText('Testing create()');

    $node->path->alias = '';
    $node->save();

    $this->drupalGet('/content/' . $node->uuid());
    $this->assertResponse(200);
    $this->assertUrl('/node/' . $node->id());
    $this->assertText('Testing create()');

    // Check try to get not existing entity.
    $this->drupalGet('/content/' . \Drupal::service('uuid')->generate());
    $this->assertResponse(404);

    // Not valid uuid.
    $this->drupalGet('/content/' . $this->randomString());
    $this->assertResponse(404);
  }

}
