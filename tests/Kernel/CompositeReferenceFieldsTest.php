<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\Tests\oe_content\Traits\CompositeReferenceTestTrait;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests composite reference fields.
 */
class CompositeReferenceFieldsTest extends RdfKernelTestBase {

  use CompositeReferenceTestTrait;

  /**
   * {@inheritdoc}
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
    'entity_reference_revisions',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['field', 'node']);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Test the composite option of entity reference fields.
   */
  public function testCompositeReferenceFields(): void {
    $entity_type_manager = $this->container->get('entity_type.manager');
    // Create a test content type.
    $type = $entity_type_manager->getStorage('node_type')->create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    $reference_field_definitions = [
      [
        'field_name' => 'entity_reference_field',
        'field_label' => 'Entity reference field',
        'revisions' => FALSE,
      ],
      [
        'field_name' => 'entity_reference_revisions_field',
        'field_label' => 'Entity reference revisions field',
        'revisions' => TRUE,
      ],
    ];

    foreach ($reference_field_definitions as $field_definition) {
      // Create an entity reference field for the test content type.
      $entity_reference_field = $this->createEntityReferenceField('node', $type->id(), $field_definition['field_name'], $field_definition['field_label'], 'node', 'default', [
        'target_bundles' => [
          $type->id() => $type->id(),
        ],
      ], 1, $field_definition['revisions']);
      // Configure the entity reference field to not be composite.
      $entity_reference_field->setThirdPartySetting('oe_content', 'composite', FALSE);
      $entity_reference_field->save();

      // Create a node that will be referenced by the others.
      $node_storage = $entity_type_manager->getStorage('node');
      $values = [
        'type' => $type->id(),
        'title' => 'Referenced node',
      ];
      /** @var \Drupal\node\NodeInterface $referenced_node */
      $referenced_node = $node_storage->create($values);
      $referenced_node->save();

      // Assert that while an entity reference field is not composite, deleting
      // a node will not delete any entities that it may be referencing.
      // Create a node that references the first node and delete it right after.
      $values = [
        'type' => $type->id(),
        'title' => 'Referencing node',
        $field_definition['field_name'] => [
          'target_id' => $referenced_node->id(),
        ],
      ];
      if ($field_definition['revisions']) {
        $values[$field_definition['field_name']]['target_revision_id'] = $referenced_node->getLoadedRevisionId();
      }
      $referencing_node = $node_storage->create($values);
      $referencing_node->save();
      $referencing_node->delete();

      // Reload the referenced node and assert it was not deleted because the
      // entity reference field is not composite yet.
      $node_storage->resetCache();
      $referenced_node = $node_storage->load($referenced_node->id());
      $this->assertNotEmpty($referenced_node);

      // Assert that while an entity reference field is composite, deleting a
      // node will not delete an entity it is referencing if another entity also
      // references the same entity.
      // Update the entity reference field configuration to be composite.
      $entity_reference_field->setThirdPartySetting('oe_content', 'composite', TRUE);
      $entity_reference_field->save();

      // Create a node that references the first one.
      $values = [
        'type' => $type->id(),
        'title' => 'Referencing node one',
        $field_definition['field_name'] => [
          'target_id' => $referenced_node->id(),
        ],
      ];
      if ($field_definition['revisions']) {
        $values[$field_definition['field_name']]['target_revision_id'] = $referenced_node->getLoadedRevisionId();
      }
      $referencing_node_one = $node_storage->create($values);
      $referencing_node_one->save();

      // Create a second node that that also references the first one and delete
      // it right after.
      $values = [
        'type' => $type->id(),
        'title' => 'Referencing node two',
        $field_definition['field_name'] => [
          'target_id' => $referenced_node->id(),
        ],
      ];
      if ($field_definition['revisions']) {
        $values[$field_definition['field_name']]['target_revision_id'] = $referenced_node->getLoadedRevisionId();
      }
      $referencing_node_two = $node_storage->create($values);
      $referencing_node_two->save();
      $referencing_node_two->delete();

      // Reload the referenced node and assert it was not deleted because it is
      // still being referenced by the first referencing node.
      $node_storage->resetCache();
      $referenced_node = $node_storage->load($referenced_node->id());
      $this->assertNotEmpty($referenced_node);

      // Assert that while an entity reference field is composite, deleting a
      // node will delete an entity it is referencing if it is not referenced by
      // any other entity.
      // Update the first referencing node to stop referencing the first node.
      $referencing_node_one->{$field_definition['field_name']}->target_id = '';
      if ($field_definition['revisions']) {
        $referencing_node_one->{$field_definition['field_name']}->target_revision_id = '';
      }
      $referencing_node_one->save();

      // Create a third referencing node that that references the first one and
      // delete it right after.
      $values = [
        'type' => $type->id(),
        'title' => 'Referencing node three',
        $field_definition['field_name'] => [
          'target_id' => $referenced_node->id(),
        ],
      ];
      if ($field_definition['revisions']) {
        $values[$field_definition['field_name']]['target_revision_id'] = $referenced_node->getLoadedRevisionId();
      }
      $referencing_node_two_three = $node_storage->create($values);
      $referencing_node_two_three->save();
      $referencing_node_two_three->delete();

      // Reload the referenced node and assert it has been deleted because there
      // are no more nodes referencing it.
      $node_storage->resetCache();
      $referenced_node = $node_storage->load($referenced_node->id());
      $this->assertEmpty($referenced_node);
    }
  }

}
