<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Test standard content entity behaviours, such as revisionability.
 */
class ContentEntityTest extends RdfKernelTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_entity',
    'node',
    'user',
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
    $this->installConfig([
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
   * Tests revisionability of content entities.
   *
   * @param string $module
   *   The module to install.
   * @param string $entity
   *   The entity ID to test.
   *
   * @dataProvider moduleEntityTypeProvider
   */
  public function testEntityRevisionability(string $module, string $entity): void {
    // Install module to test.
    $this->installModule($module);
    $this->installEntitySchema($entity);
    $this->installConfig([$module]);

    // Create a custom entity type.
    $bundle_storage = $this->entityTypeManager->getStorage($entity . '_type');
    $entity_type = $bundle_storage->create([
      'label' => 'Test custom entity type',
      'id' => 'test_custom_entity_type',
    ]);
    $entity_type->save();

    // Assert that the custom entity type is created.
    $entity_type = $bundle_storage->load($entity_type->id());
    $this->assertEquals('Test custom entity type', $entity_type->label());
    $this->assertEquals('test_custom_entity_type', $entity_type->id());

    // Create a custom entity of the type above.
    $entity_storage = $this->entityTypeManager->getStorage($entity);
    $values = [
      'bundle' => $entity_type->id(),
      'name' => 'My test entity',
    ];
    $custom_entity = $entity_storage->create($values);
    $custom_entity->save();

    // Assert the creation of a custom entity.
    $custom_entity = $entity_storage->load($custom_entity->id());
    $this->assertEquals('My test entity', $custom_entity->getName());
    $this->assertEquals($entity_type->id(), $custom_entity->bundle());

    // Create the second revision.
    $custom_entity->setNewRevision(TRUE);
    $custom_entity->setRevisionCreationTime(1576752888);
    $custom_entity->setRevisionUserId(1);
    $custom_entity->setRevisionLogMessage('This is my log message');
    $custom_entity->setName('My test entity 2');
    $custom_entity->save();

    // Load the second revision.
    $custom_entity = $entity_storage->loadRevision(2);

    // Assert that the revision was correctly created.
    $this->assertEquals(1576752888, $custom_entity->getRevisionCreationTime());
    $this->assertEquals(1, $custom_entity->getRevisionUserId());
    $this->assertEquals('My test entity 2', $custom_entity->getName());
    $this->assertEquals('This is my log message', $custom_entity->getRevisionLogMessage());

    // Load the first revision.
    $custom_entity = $entity_storage->loadRevision(1);
    $this->assertEquals('My test entity', $custom_entity->getName());
  }

  /**
   * Provide module / entity type paris to run content entity tests.
   *
   * @return array
   *   List of module / entity type paris.
   */
  public function moduleEntityTypeProvider(): array {
    return [
      ['oe_content_entity_contact', 'oe_contact'],
      ['oe_content_entity_organisation', 'oe_organisation'],
      ['oe_content_entity_venue', 'oe_venue'],
    ];
  }

}