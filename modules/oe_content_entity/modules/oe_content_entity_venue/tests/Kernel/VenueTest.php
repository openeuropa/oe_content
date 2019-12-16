<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the venue entity.
 */
class VenueTest extends RdfKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_venue',
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
    $this->installEntitySchema('oe_venue');
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
      'oe_content_entity_venue',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Tests venue entities.
   */
  public function testVenue(): void {
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    // Create a venue type.
    $venue_type_storage = $entity_type_manager->getStorage('oe_venue_type');
    $venue_type = $venue_type_storage->create(['label' => 'Test venue type', 'id' => 'test_venue_type']);
    $venue_type->save();

    $venue_type = $venue_type_storage->load($venue_type->id());
    $this->assertEquals('Test venue type', $venue_type->label());
    $this->assertEquals('test_venue_type', $venue_type->id());

    // Create a venue.
    $venue_storage = $entity_type_manager->getStorage('oe_venue');
    $values = [
      'bundle' => $venue_type->id(),
      'name' => 'My venue',
    ];
    /** @var \Drupal\oe_content_event\Entity\EventProfileInterface $event_profile */
    $event_profile = $venue_storage->create($values);
    $event_profile->save();

    $event_profile = $venue_storage->load($event_profile->id());
    $this->assertEquals('My venue', $event_profile->getName());
    $this->assertEquals($venue_type->id(), $event_profile->bundle());
  }

}
