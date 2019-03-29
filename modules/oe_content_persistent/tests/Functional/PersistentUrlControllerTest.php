<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Functional;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Test PersistentUrlController response with caching mechanism.
 *
 * @group oe_content
 */
class PersistentUrlControllerTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'path',
    'node',
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

    ConfigurableLanguage::create(['id' => 'fr'])->save();

    $config = $this->config('language.negotiation');
    $config->set('url.prefixes', ['en' => '', 'fr' => 'fr'])
      ->save();
  }

  /**
   * Tests the path cache with Persistent Controller.
   */
  public function testPersistentUrlController(): void {

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

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

    $node->path->alias = '/foo2';
    $node->save();

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

    // Add a translation, verify it is being saved as expected.
    $translation = $node->addTranslation('fr', $node->toArray());
    $translation->get('path')->alias = '/petitbar';
    $translation->save();

    $this->drupalGet('/fr/content/' . $node->uuid());
    $this->assertResponse(200);
    $this->assertUrl('/fr/petitbar');
    $this->assertText('Testing create()');

    $node = $node_storage->load($node->id());
    $node->path->alias = '/original_en';
    $node->save();

    $this->drupalGet('/content/' . $node->uuid());
    $this->assertResponse(200);
    $this->assertUrl('/original_en');
    $this->assertText('Testing create()');

    // Not valid uuid.
    $this->drupalGet('/content/' . $this->randomString());
    $this->assertResponse(404);
  }

}
