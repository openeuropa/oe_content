<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the custom content entities.
 */
class CustomContentEntityTest extends RdfKernelTestBase {

  /**
   * The entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_contact',
    'entity_reference_revisions',
    'inline_entity_form',
    'node',
    'user',
    'maxlength',
    'menu_ui',
    'media',
    'options',
    'address',
    'field',
    'field_group',
    'image',
    'link',
    'typed_link',
    'rdf_skos',
    'system',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('oe_contact');
    $this->installSchema('node', 'node_access');
    $this->installConfig([
      'address',
      'field',
      'field_group',
      'filter',
      'link',
      'typed_link',
      'maxlength',
      'node',
      'system',
      'rdf_skos',
      'oe_content',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();

    // Install the module and entity from data provider.
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Tests custom content entities.
   *
   * @param string $module
   *   The module to install.
   * @param string $entity
   *   The entity ID to test.
   *
   * @dataProvider testDataProvider
   */
  public function testCustomContentEntities(string $module, string $entity): void {
    // Install module to test.
    $this->installModule($module);
    $this->installEntitySchema($entity);
    $this->installConfig([$module]);

    // Create a custom entity type.
    $custom_entity_type_storage = $this->entityTypeManager->getStorage($entity . '_type');
    $custom_entity_type = $custom_entity_type_storage->create(['label' => 'Test custom entity type', 'id' => 'test_custom_entity_type']);
    $custom_entity_type->save();
    // Assert the custom entity type is created.
    $custom_entity_type = $custom_entity_type_storage->load($custom_entity_type->id());
    $this->assertEquals('Test custom entity type', $custom_entity_type->label());
    $this->assertEquals('test_custom_entity_type', $custom_entity_type->id());

    // Create a custom entity of the type above.
    $custom_entity_storage = $this->entityTypeManager->getStorage($entity);
    $values = [
      'bundle' => $custom_entity_type->id(),
      'name' => 'My test entity',
    ];
    $custom_entity = $custom_entity_storage->create($values);
    $custom_entity->save();
    // Assert the created custom entity.
    $custom_entity = $custom_entity_storage->load($custom_entity->id());
    $this->assertEquals('My test entity', $custom_entity->getName());
    $this->assertEquals($custom_entity_type->id(), $custom_entity->bundle());

    // Create the second revision.
    $timestamp = 1576752888;
    $custom_entity->setNewRevision(TRUE);
    $custom_entity->setRevisionCreationTime($timestamp);
    $custom_entity->setRevisionUserId(1);
    $custom_entity->setRevisionLogMessage('This is my log message');
    $custom_entity->save();

    // Load the new revision.
    $custom_entity = $custom_entity_storage->loadRevision($custom_entity->getRevisionId());

    // Assert the revision values were correctly saved.
    $this->assertEquals($timestamp, $custom_entity->getRevisionCreationTime());
    $this->assertEquals(1, $custom_entity->getRevisionUserId());
    $this->assertEquals('This is my log message', $custom_entity->getRevisionLogMessage());
  }

  /**
   * Data provider for custom content entity tests.
   *
   * @return array
   *   A set of dump data for testing.
   */
  public function testDataProvider(): array {
    return [
      ['oe_content_entity_contact', 'oe_contact'],
      ['oe_content_entity_organisation', 'oe_organisation'],
      ['oe_content_entity_venue', 'oe_venue'],
    ];
  }

}
