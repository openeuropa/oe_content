<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel;

use Drupal\node\Entity\Node;
use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Tests the content type base field definitions.
 */
class EventTest extends RdfKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'field_group',
    'datetime_range',
    'entity_reference_revisions',
    'link',
    'image',
    'inline_entity_form',
    'node',
    'maxlength',
    'media',
    'oe_media',
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
    'oe_content_event',
    'options',
    'rdf_skos',
    'system',
    'text',
    'typed_link',
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
    $this->installEntitySchema('media');
    $this->installEntitySchema('oe_contact');
    $this->installEntitySchema('oe_organisation');
    $this->installEntitySchema('oe_venue');
    $this->installConfig(['field', 'node', 'oe_content', 'oe_content_event']);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Test the Organisation fields.
   */
  public function testOrganisationFields(): void {
    $values = [
      'type' => 'oe_event',
      'title' => 'My node title',
      'oe_event_organiser_is_internal' => 0,
      'oe_event_organiser_name' => 'Organisation',
      'oe_event_organiser_internal' => 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT',
    ];

    // Create node.
    $node = Node::create($values);
    $node->save();

    // Assert that the internal value has been cleared.
    $this->assertNull($node->get('oe_event_organiser_internal')->value);
    $this->assertEquals('Organisation', $node->get('oe_event_organiser_name')->value);

    // Set all the 3 fields and set the internal organiser to checked.
    $node->set('oe_event_organiser_is_internal', 1);
    $node->set('oe_event_organiser_name', 'Organisation');
    $node->set('oe_event_organiser_internal', 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT');
    $node->save();

    // Assert that the internal organiser value has been kept.
    $this->assertNull($node->get('oe_event_organiser_name')->value);
    $this->assertEquals('Directorate-General for Informatics', $node->get('oe_event_organiser_internal')->entity->label());

    // Set all the 3 fields and set the internal organiser to checked.
    $node->set('oe_event_organiser_is_internal', NULL);
    $node->set('oe_event_organiser_name', 'Organisation');
    $node->set('oe_event_organiser_internal', 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT');
    $node->save();

    // Assert that on NULL, both values are kept.
    $this->assertEquals('Organisation', $node->get('oe_event_organiser_name')->value);
    $this->assertEquals('Directorate-General for Informatics', $node->get('oe_event_organiser_internal')->entity->label());
  }

}
