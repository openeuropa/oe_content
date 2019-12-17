<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the contact entity.
 */
class ContactTest extends RdfKernelTestBase {

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
      'oe_content_entity_contact',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Tests contact entities.
   */
  public function testContact(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Create a contact type.
    $contact_type_storage = $entity_type_manager->getStorage('oe_contact_type');
    $contact_type = $contact_type_storage->create(['label' => 'Test contact type', 'id' => 'test_contact_type']);
    $contact_type->save();

    $contact_type = $contact_type_storage->load($contact_type->id());
    $this->assertEquals('Test contact type', $contact_type->label());
    $this->assertEquals('test_contact_type', $contact_type->id());

    // Create a contact.
    $contact_storage = $entity_type_manager->getStorage('oe_contact');
    $values = [
      'bundle' => $contact_type->id(),
      'name' => 'My contact',
    ];
    /** @var \Drupal\oe_content_entity_contact\Entity\Contact $contact_entity */
    $contact_entity = $contact_storage->create($values);
    $contact_entity->save();

    $contact_entity = $contact_storage->load($contact_entity->id());
    $this->assertEquals('My contact', $contact_entity->getName());
    $this->assertEquals($contact_type->id(), $contact_entity->bundle());
  }

}
