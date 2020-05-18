<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests composite reference fields.
 */
class CompositeFieldsTest extends RdfKernelTestBase {

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
  public function testCompositeOption(): void {
    $entity_type_manager = \Drupal::entityTypeManager();
    // Create a content type.
    $type = $entity_type_manager->getStorage('node_type')->create(['name' => 'Test content type', 'type' => 'test_ct']);
    $type->save();

    // Create an entity reference field.
    $entity_type_manager->getStorage('field_storage_config')->create([
      'entity_type' => 'node',
      'field_name' => 'entity_reference_field',
      'type' => 'entity_reference',
      'cardinality' => 1,
    ])->save();
    /** @var \Drupal\field\FieldConfigInterface $entity_reference_field */
    $entity_reference_field = $entity_type_manager->getStorage('field_config')->create([
      'entity_type' => 'node',
      'field_name' => 'entity_reference_field',
      'bundle' => 'test_ct',
      'label' => 'Entity reference field',
      'translatable' => FALSE,
      'settings' => [
        'handler' => 'default:node',
        'handler_settings' => [
          'target_bundles' => [
            'test_ct' => 'test_ct',
          ],
        ],
      ],
    ]);
    $entity_reference_field->setThirdPartySetting('oe_content', 'composite', FALSE);
    $entity_reference_field->save();

    $node_storage = $entity_type_manager->getStorage('node');
    // Create an entity to be referenced.
    $values = [
      'type' => 'test_ct',
      'title' => 'Referenced entity',
    ];
    $referenced_node = $node_storage->create($values);
    $referenced_node->save();

    // Create a node that has a reference and delete it.
    $values = [
      'type' => 'test_ct',
      'title' => 'Temporary entity',
      'entity_reference_field' => [
        'target_id' => $referenced_node->id(),
      ],
    ];
    $temporary_node = $node_storage->create($values);
    $temporary_node->save();
    $temporary_node->delete();

    // Reload the referenced node and assert it was not deleted because
    // the field is not composite yet.
    $node_storage->resetCache();
    $referenced_node = $node_storage->load($referenced_node->id());
    $this->assertNotEmpty($referenced_node);

    // Update the field configuration to be composite.
    $entity_reference_field->setThirdPartySetting('oe_content', 'composite', TRUE);
    $entity_reference_field->save();

    // Create a node to keep the reference.
    $values = [
      'type' => 'test_ct',
      'title' => 'Referencing entity',
      'entity_reference_field' => [
        'target_id' => $referenced_node->id(),
      ],
    ];
    $referencing_node = $node_storage->create($values);
    $referencing_node->save();

    // Create a node that has a reference and delete it.
    $values = [
      'type' => 'test_ct',
      'title' => 'Temporary entity',
      'entity_reference_field' => [
        'target_id' => $referenced_node->id(),
      ],
    ];
    $temporary_node = $node_storage->create($values);
    $temporary_node->save();
    $temporary_node->delete();

    // Reload the referenced node and assert it was not deleted because
    // the field is still referenced by another node.
    $node_storage->resetCache();
    $referenced_node = $node_storage->load($referenced_node->id());
    $this->assertNotEmpty($referenced_node);

    // Update the referencing node to stop referencing the entity.
    $referencing_node->entity_reference_field->target_id = '';
    $referencing_node->save();

    // Create a node that has a reference and delete it.
    $values = [
      'type' => 'test_ct',
      'title' => 'Temporary entity',
      'entity_reference_field' => [
        'target_id' => $referenced_node->id(),
      ],
    ];
    $temporary_node = $node_storage->create($values);
    $temporary_node->save();
    $temporary_node->delete();

    // Reload the referenced node and assert it has been deleted because
    // there are no more entities referencing it.
    $node_storage->resetCache();
    $referenced_node = $node_storage->load($referenced_node->id());
    $this->assertEmpty($referenced_node);
  }

}
