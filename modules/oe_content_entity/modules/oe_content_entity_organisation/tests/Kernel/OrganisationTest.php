<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the organisation entity.
 */
class OrganisationTest extends RdfKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_organisation',
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
    $this->installEntitySchema('oe_organisation');
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
      'oe_content_entity_organisation',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Tests organisation entities.
   */
  public function testOrganisation(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Create a organisation type.
    $organisation_type_storage = $entity_type_manager->getStorage('oe_organisation_type');
    $organisation_type = $organisation_type_storage->create(['label' => 'Test organisation type', 'id' => 'test_organisation_type']);
    $organisation_type->save();

    $organisation_type = $organisation_type_storage->load($organisation_type->id());
    $this->assertEquals('Test organisation type', $organisation_type->label());
    $this->assertEquals('test_organisation_type', $organisation_type->id());

    // Create a organisation.
    $organisation_storage = $entity_type_manager->getStorage('oe_organisation');
    $values = [
      'bundle' => $organisation_type->id(),
      'name' => 'My organisation',
    ];
    /** @var \Drupal\oe_content_entity_organisation\Entity\Organisation $organisation_entity */
    $organisation_entity = $organisation_storage->create($values);
    $organisation_entity->save();

    $organisation_entity = $organisation_storage->load($organisation_entity->id());
    $this->assertEquals('My organisation', $organisation_entity->getName());
    $this->assertEquals($organisation_type->id(), $organisation_entity->bundle());
  }

}
