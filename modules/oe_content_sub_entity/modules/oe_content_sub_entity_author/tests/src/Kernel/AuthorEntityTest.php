<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_author\Kernel;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Tests all author entity bundles.
 */
class AuthorEntityTest extends SparqlKernelTestBase {

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
    'description_list_field',
    'inline_entity_form',
    'oe_content_entity_contact',
    'oe_content_social_media_links_field',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_person',
    'oe_content_timeline_field',
    'oe_content_organisation',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_content_sub_entity_author',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('oe_author');
    $this->installEntitySchema('oe_document_reference');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installConfig([
      'node',
      'filter',
      'media',
      'oe_content_social_media_links_field',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_person',
      'oe_content_organisation',
      'oe_content_sub_entity_author',
    ]);

    \Drupal::moduleHandler()->loadInclude('oe_content', 'install');
    oe_content_install(FALSE);
    \Drupal::moduleHandler()->loadInclude('oe_content_sub_entity_author', 'install');
    oe_content_sub_entity_author_install(FALSE);
  }

  /**
   * Tests label of extra author entities.
   */
  public function testLabel(): void {
    foreach ($this->getAuthorEntitiesTestData() as $bundle => $data_case) {
      $author = $this->container->get('entity_type.manager')->getStorage('oe_author')->create([
        'type' => $bundle,
      ]);
      foreach ($data_case as $data) {
        $field_values = NULL;
        if (!empty($data['referenced_nodes'])) {
          foreach ($data['referenced_nodes'] as $node_values) {
            $field_values[] = $this->createNode($node_values);
          }
        }
        elseif (!empty($data['field_values'])) {
          $field_values = $data['field_values'];
        }
        elseif (!empty($data['corporate_bodies'])) {
          $field_values = $data['corporate_bodies'];
        }
        elseif (!empty($data['political_leaders'])) {
          $field_values = $data['political_leaders'];
        }
        $author->set($data['reference_field_name'], $field_values);
        $author->save();
        $this->assertEquals($data['expected_label'], $author->label());
      }
    }
  }

  /**
   * Testing data for author entity type.
   *
   * @return array
   *   Array of test cases.
   */
  protected function getAuthorEntitiesTestData(): array {
    return [
      'oe_political_leader' => [
        [
          'reference_field_name' => 'oe_skos_reference',
          'political_leaders' => NULL,
          'expected_label' => 'EU Political leader',
        ],
        [
          'reference_field_name' => 'oe_skos_reference',
          'political_leaders' => [
            'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0440FF',
          ],
          'expected_label' => 'Ursula von der Leyen',
        ],
        [
          'reference_field_name' => 'oe_skos_reference',
          'political_leaders' => [
            'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0440FF',
            'http://publications.europa.eu/resource/authority/political-leader/COM_00006A044747',
          ],
          'expected_label' => 'Ursula von der Leyen, Nicolas Schmit',
        ],
      ],
      'oe_corporate_body' => [
        [
          'reference_field_name' => 'oe_skos_reference',
          'corporate_bodies' => NULL,
          'expected_label' => 'Corporate body',
        ],
        [
          'reference_field_name' => 'oe_skos_reference',
          'corporate_bodies' => [
            'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
          ],
          'expected_label' => 'Directorate-General for Budget',
        ],
        [
          'reference_field_name' => 'oe_skos_reference',
          'corporate_bodies' => [
            'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
            'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
          ],
          'expected_label' => 'Directorate-General for Budget, Audit Board of the European Communities',
        ],
      ],
      'oe_person' => [
        [
          'reference_field_name' => 'oe_node_reference',
          'referenced_nodes' => NULL,
          'expected_label' => 'Person',
        ],
        [
          'reference_field_name' => 'oe_node_reference',
          'referenced_nodes' => [
            [
              'type' => 'oe_person',
              'oe_person_first_name' => 'John',
              'oe_person_last_name' => 'Doe',
            ],
          ],
          'expected_label' => 'John Doe',
        ],
        [
          'reference_field_name' => 'oe_node_reference',
          'referenced_nodes' => [
            [
              'type' => 'oe_person',
              'oe_person_first_name' => 'John',
              'oe_person_last_name' => 'Doe',
            ],
            [
              'type' => 'oe_person',
              'oe_person_first_name' => 'Foo',
              'oe_person_last_name' => 'Bar',
            ],
          ],
          'expected_label' => 'John Doe, Foo Bar',
        ],
      ],
      'oe_organisation' => [
        [
          'reference_field_name' => 'oe_node_reference',
          'referenced_nodes' => NULL,
          'expected_label' => 'Organisation',
        ],
        [
          'reference_field_name' => 'oe_node_reference',
          'referenced_nodes' => [
            [
              'type' => 'oe_organisation',
              'title' => 'European Commission',
            ],
          ],
          'expected_label' => 'European Commission',
        ],
        [
          'reference_field_name' => 'oe_node_reference',
          'referenced_nodes' => [
            [
              'type' => 'oe_organisation',
              'title' => 'Org 1',
            ],
            [
              'type' => 'oe_organisation',
              'title' => 'Org 2',
            ],
          ],
          'expected_label' => 'Org 1, Org 2',
        ],
      ],
      'oe_link' => [
        [
          'reference_field_name' => 'oe_link',
          'field_values' => NULL,
          'expected_label' => 'Link',
        ],
        [
          'reference_field_name' => 'oe_link',
          'field_values' => [
            [
              'title' => 'Link Title',
              'uri' => 'http://example.com',
            ],
          ],
          'expected_label' => 'Link Title',
        ],
        [
          'reference_field_name' => 'oe_link',
          'field_values' => [
            [
              'title' => 'Link Title 1',
              'uri' => 'http://example.com',
            ],
            [
              'title' => 'Link Title 2',
              'uri' => 'http://example.com',
            ],
          ],
          'expected_label' => 'Link Title 1, Link Title 2',
        ],
      ],
    ];
  }

}
