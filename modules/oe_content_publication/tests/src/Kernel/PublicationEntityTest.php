<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_publication\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests the Publication content type.
 */
class PublicationEntityTest extends EntityKernelTestBase {

  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'composite_reference',
    'datetime',
    'entity_reference_revisions',
    'field_group',
    'file',
    'node',
    'link',
    'maxlength',
    'media',
    'options',
    'rdf_skos',
    'image',
    'inline_entity_form',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_documents_field',
    'oe_content_reference_code_field',
    'oe_content_entity_contact',
    'oe_content_publication',
    'oe_content_social_media_links_field',
    'oe_content_sub_entity_document_reference',
    'oe_content_timeline_field',
    'file_link',
    'oe_media',
    'typed_link',
    'sparql_entity_storage',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSparql();

    $entities = [
      'node',
      'media',
      'file',
      'oe_contact',
      'oe_document_reference',
    ];
    foreach ($entities as $entity) {
      $this->installEntitySchema($entity);
    }

    module_load_include('install', 'oe_content_documents_field');
    oe_content_documents_field_install(FALSE);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);

    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_reference_code_field',
      'oe_content_social_media_links_field',
      'oe_content_publication',
      'oe_media',
    ]);

    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);
  }

  /**
   * Tests the Publication content entity presave logic.
   */
  public function testPublicationEntityPreSave() {
    // Create a publication.
    /** @var \Drupal\node\Entity\Node $node */
    $node = Node::create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => [
        'http://publications.europa.eu/resource/authority/resource-type/DIR_DEL',
      ],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_author' => [
        'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();

    // Create a document for Publication.
    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_media') . '/tests/fixtures/sample.pdf'), "public://sample.pdf");
    $file->setPermanent();
    $file->save();

    $media = \Drupal::service('entity_type.manager')->getStorage('media')->create([
      'bundle' => 'document',
      'name' => "Test document",
      'oe_media_file_type' => 'local',
      'oe_media_file' => [
        'target_id' => (int) $file->id(),
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $media->save();

    $node_storage = \Drupal::service('entity_type.manager')->getStorage('node');

    // Create a Publication node with required fields only.
    /** @var \Drupal\node\Entity\Node $node */
    $collection = $node_storage->create([
      'type' => 'oe_publication',
      'title' => 'Test Publication node',
      'oe_teaser' => 'Test teaser text.',
      'oe_publication_type' => 'http://publications.europa.eu/resource/authority/resource-type/ABSTRACT_JUR',
      'oe_publication_collection' => 0,
      'oe_documents' => [$media],
      'oe_publication_publications' => [$node],
      'oe_publication_date' => [
        'value' => '2020-04-15',
      ],
      'oe_subject' => 'http://data.europa.eu/uxp/1000',
      'oe_author' => 'http://publications.europa.eu/resource/authority/corporate-body/AASM',
      'oe_content_content_owner' => 'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'uid' => 0,
      'status' => 1,
    ]);
    $collection->save();

    // Assert that the value of the publications reference is not saved because
    // the publication is not a collection.
    $this->assertTrue($collection->get('oe_publication_publications')->isEmpty());
    $this->assertFalse($collection->get('oe_documents')->isEmpty());

    // Now switch the publication to collection and assert the documents field.
    $collection->set('oe_publication_collection', 1)->save();
    $this->assertTrue($collection->get('oe_documents')->isEmpty());
  }

}
