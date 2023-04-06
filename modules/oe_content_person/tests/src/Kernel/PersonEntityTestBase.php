<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_person\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Base class for Person content type tests.
 */
abstract class PersonEntityTestBase extends EntityKernelTestBase {

  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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
    'oe_content_entity_contact',
    'oe_content_person',
    'oe_content_sub_entity',
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

    $this->installConfig([
      'oe_content',
      'oe_content_departments_field',
      'oe_content_social_media_links_field',
      'oe_content_person',
      'oe_media',
    ]);

    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);

    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

}
