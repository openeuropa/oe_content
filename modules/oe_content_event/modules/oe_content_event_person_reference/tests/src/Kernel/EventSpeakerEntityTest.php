<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event_person_reference\Kernel;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Tests Event speaker entity.
 */
class EventSpeakerEntityTest extends SparqlKernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'link',
    'entity_reference_revisions',
    'sparql_entity_storage',
    'user',
    'rdf_skos',
    'composite_reference',
    'media',
    'image',
    'text',
    'options',
    'datetime',
    'typed_link',
    'field_group',
    'maxlength',
    'system',
    'inline_entity_form',
    'oe_content_entity_contact',
    'oe_content_social_media_links_field',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_person',
    'oe_content_timeline_field',
    'oe_content_sub_entity',
    'oe_content_event_person_reference',
    'oe_content_sub_entity_document_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('oe_event_speaker');
    $this->installEntitySchema('node');
    $this->installConfig([
      'node',
      'filter',
      'oe_content_social_media_links_field',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_person',
      'oe_content_event_person_reference',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

  /**
   * Tests label of event speaker entities.
   */
  public function testLabel(): void {
    foreach ($this->getEventSpeakerEntitiesTestData() as $bundle => $data_case) {
      $author = $this->container->get('entity_type.manager')->getStorage('oe_event_speaker')->create([
        'type' => $bundle,
      ]);
      foreach ($data_case as $data) {
        $field_values = NULL;
        if (!empty($data['referenced_nodes'])) {
          foreach ($data['referenced_nodes'] as $node_values) {
            $field_values[] = $this->createNode($node_values);
          }
        }
        $author->set($data['reference_field_name'], $field_values);
        $author->save();
        $this->assertEquals($data['expected_label'], $author->label());
      }
    }
  }

  /**
   * Testing data for event speaker entity type.
   *
   * @return array
   *   Array of test cases.
   */
  protected function getEventSpeakerEntitiesTestData(): array {
    return [
      'oe_default' => [
        [
          'reference_field_name' => 'oe_person',
          'referenced_nodes' => NULL,
          'expected_label' => 'Default',
        ],
        [
          'reference_field_name' => 'oe_person',
          'referenced_nodes' => [
            [
              'type' => 'oe_person',
              'oe_person_first_name' => 'John',
              'oe_person_last_name' => 'Doe',
            ],
          ],
          'expected_label' => 'John Doe',
        ],
      ],
    ];
  }

}
