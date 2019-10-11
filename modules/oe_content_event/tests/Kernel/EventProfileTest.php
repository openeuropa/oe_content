<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the Event profile entity.
 */
class EventProfileTest extends RdfKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_event',
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
    $this->installEntitySchema('event_venue');
    $this->installEntitySchema('event_profile');
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
      'oe_content_event',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Tests Event profile entities.
   */
  public function testEventProfile(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Create an event profile type.
    $event_profile_type_storage = $entity_type_manager->getStorage('event_profile_type');
    $event_profile_type = $event_profile_type_storage->create(['label' => 'Test event profile type', 'id' => 'test_event_profile_type']);
    $event_profile_type->save();

    $event_profile_type = $event_profile_type_storage->load($event_profile_type->id());
    $this->assertEquals('Test event profile type', $event_profile_type->label());
    $this->assertEquals('test_event_profile_type', $event_profile_type->id());

    // Create an event profile.
    $event_profile_storage = $entity_type_manager->getStorage('event_profile');
    $values = [
      'bundle' => $event_profile_type->id(),
      'name' => 'My event profile',
    ];
    /** @var \Drupal\oe_content_event\Entity\EventProfileInterface $event_profile */
    $event_profile = $event_profile_storage->create($values);
    $event_profile->save();

    $event_profile = $event_profile_storage->load($event_profile->id());
    $this->assertEquals('My event profile', $event_profile->getName());
    $this->assertEquals($event_profile_type->id(), $event_profile->bundle());
  }

}
