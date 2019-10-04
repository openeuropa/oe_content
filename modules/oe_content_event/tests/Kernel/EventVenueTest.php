<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the event venue entity.
 */
class EventVenueTest extends RdfKernelTestBase {

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
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('event_venue');
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
   * Tests event venue entity.
   */
  public function testEventVenueEntity(): void {
    /** @var \Drupal\oe_content_event\EventVenueStorageInterface $event_venue_storage */
    $event_venue_storage = $this->container->get('entity_type.manager')->getStorage('event_venue');

    // Create an event venue.
    /** @var \Drupal\oe_content_event\Entity\EventVenueInterface $event_venue_entity */
    $event_venue_entity = $event_venue_storage->create([
      'name' => 'Event venue',
      'capacity' => '100 seats',
      'room' => 'RM/05/001',
      'postal_address' => [
        'country_code' => 'BE',
        'locality' => 'Brussels',
        'postal_code' => '1000',
        'address_line1' => 'Rue Belliard 28',
        'organization' => 'European Commission',
      ],
      'status' => 1,
    ]);
    $event_venue_entity->save();

    $event_venue_entity = $event_venue_storage->load($event_venue_entity->id());

    // Asserts that event venue was correctly saved.
    $this->assertEquals(1, $event_venue_entity->getRevisionId());
    $this->assertEqual($event_venue_entity->get('name')->value, 'Event venue');
    $this->assertEqual($event_venue_entity->get('capacity')->value, '100 seats');
    $this->assertEqual($event_venue_entity->get('room')->value, 'RM/05/001');
    $this->assertEqual($event_venue_entity->get('postal_address')->country_code, 'BE');
    $this->assertEqual($event_venue_entity->get('postal_address')->locality, 'Brussels');
    $this->assertEqual($event_venue_entity->get('postal_address')->postal_code, '1000');
    $this->assertEqual($event_venue_entity->get('postal_address')->address_line1, 'Rue Belliard 28');
    $this->assertEqual($event_venue_entity->get('postal_address')->organization, 'European Commission');
  }

}
