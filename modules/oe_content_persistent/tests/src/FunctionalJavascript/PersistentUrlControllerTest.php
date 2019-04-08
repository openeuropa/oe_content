<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests the PersistentUrlController response.
 *
 * @group oe_content
 *
 * Usage of FunctionalJavascript test based on problem of phpunit settings.
 */
class PersistentUrlControllerTest extends WebDriverTestBase {

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

    $session = $this->getSession();
    $page = $session->getPage();

    $this->drupalGet('/content/' . $node->uuid());
    $this->assertUrl('/foo');
    $page->hasContent('Testing create()');

    $node = \Drupal::service('entity_type.manager')->getStorage('node')->load($node->id());
    $node->path->alias = '/foo2';
    $node->save();

    // Ensure the cache is invalidated correctly.
    $this->drupalGet('/content/' . $node->uuid());
    $this->assertUrl('/foo2');
    $page->hasContent('Testing create()');

    $node->path->alias = '';
    $node->save();

    $this->drupalGet('/content/' . $node->uuid());
    $this->assertUrl('/node/' . $node->id());
    $page->hasContent('Testing create()');

    // Check try to get not existing entity.
    $this->drupalGet('/content/' . \Drupal::service('uuid')->generate());
    $this->assertFalse($page->hasContent('Testing create()'));

    // Not valid uuid.
    $this->drupalGet('/content/' . $this->randomString());
    $this->assertFalse($page->hasContent('Testing create()'));
  }

}
