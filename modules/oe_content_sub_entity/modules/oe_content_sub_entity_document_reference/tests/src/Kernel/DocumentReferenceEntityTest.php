<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_document_reference\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\field\Traits\EntityReferenceFieldCreationTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests document reference entity.
 */
class DocumentReferenceEntityTest extends EntityKernelTestBase {

  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceFieldCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('oe_document_reference');
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);

    // Create a node type.
    $this->createContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);

    // Create test bundle of Document reference entity.
    $type_storage = $this->container->get('entity_type.manager')->getStorage('oe_document_reference_type');
    $type_storage->create([
      'id' => 'test_bundle',
      'label' => 'Test bundle',
    ])->save();
    $this->createEntityReferenceField('oe_document_reference', 'test_bundle', 'field_reference_1', 'Node reference 1', 'node');
    $this->createEntityReferenceField('oe_document_reference', 'test_bundle', 'field_reference_2', 'Node reference 2', 'node');
  }

  /**
   * Tests label of Document reference entity.
   */
  public function testLabel() {
    // Document reference shows bundle label if there aren't referenced
    // entities.
    $document_reference = $this->entityTypeManager->getStorage('oe_document_reference')->create([
      'type' => 'test_bundle',
    ]);
    $document_reference->save();
    $this->assertEquals('Test bundle', $document_reference->label());

    // Document reference shows referenced entity label if it exists.
    $node_1 = $this->createNode([
      'title' => 'Referenced node 1 label',
    ]);
    $document_reference->set('field_reference_1', [$node_1])->save();
    $this->assertEquals('Referenced node 1 label', $document_reference->label());

    // Document reference shows referenced entity labels separated by comma,
    // if they exist.
    $node_2 = $this->createNode([
      'title' => 'Referenced node 2 label',
    ]);
    $document_reference->set('field_reference_2', [$node_2])->save();
    $this->assertEquals('Referenced node 1 label, Referenced node 2 label', $document_reference->label());
  }

}
