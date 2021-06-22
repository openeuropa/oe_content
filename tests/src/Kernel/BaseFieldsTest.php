<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Tests the content type base field definitions.
 */
class BaseFieldsTest extends SparqlKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'link',
    'node',
    'oe_content',
    'rdf_skos',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node']);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

  /**
   * Test the defined fields.
   */
  public function testBaseStorage(): void {
    // Create content type.
    $type = NodeType::create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    $values = [
      'type' => 'test_ct',
      'title' => 'My node title',
      'oe_content_short_title' => 'My short title',
      'oe_content_navigation_title' => 'My navigation title',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT',
      'oe_content_legacy_link' => 'http://legacy-link.com',
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    $entity_type_manager = \Drupal::entityTypeManager()->getStorage('node');
    $entity_type_manager->resetCache();
    /** @var \Drupal\node\NodeInterface $node */
    $node = $entity_type_manager->load($node->id());

    // Assert the base field values.
    $this->assertEquals('My node title', $node->label());
    $this->assertEquals('My short title', $node->get('oe_content_short_title')->value);
    $this->assertEquals('My navigation title', $node->get('oe_content_navigation_title')->value);
    $this->assertEquals('Directorate-General for Informatics', $node->get('oe_content_content_owner')->entity->label());
    $this->assertEquals('http://legacy-link.com', $node->get('oe_content_legacy_link')->uri);
  }

}
