<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_author\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;

/**
 * Tests author reference entity.
 */
class AuthorEntityTest extends EntityKernelTestBase {

  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'oe_content_sub_entity_author',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('oe_author');
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);

    // Create a node type.
    $this->createContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);

    // Create test bundle of Author reference entity.
    $type_storage = $this->container->get('entity_type.manager')->getStorage('oe_author_type');
    $type_storage->create([
      'id' => 'test_bundle',
      'label' => 'Test bundle',
    ])->save();
    $this->createEntityReferenceField('oe_author', 'test_bundle', 'field_reference_1', 'Node reference 1', 'node');
    $this->createEntityReferenceField('oe_author', 'test_bundle', 'field_reference_2', 'Node reference 1', 'node');

  }

  /**
   * Tests label of Author reference entity.
   */
  public function testLabel() {
    // Author reference shows bundle label if there aren't referenced
    // entities.
    $author_reference = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'test_bundle',
    ]);
    $author_reference->save();
    $this->assertEquals('Test bundle', $author_reference->label());

    // Author reference shows referenced entity label if it exists.
    $node_1 = $this->createNode([
      'title' => 'Referenced node 1 label',
    ]);
    $author_reference->set('field_reference_1', [$node_1])->save();
    $this->assertEquals('Referenced node 1 label', $author_reference->label());

    // Author reference shows referenced entity labels separated by comma,
    // if they exist.
    $node_2 = $this->createNode([
      'title' => 'Referenced node 2 label',
    ]);
    $author_reference->set('field_reference_2', [$node_2])->save();
    $this->assertEquals('Referenced node 1 label, Referenced node 2 label', $author_reference->label());
  }

}
