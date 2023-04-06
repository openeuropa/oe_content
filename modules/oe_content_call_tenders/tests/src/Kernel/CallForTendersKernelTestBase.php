<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_call_tenders\Kernel;

use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Base test class for "Call for tenders" content type kernel tests.
 */
abstract class CallForTendersKernelTestBase extends SparqlKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'datetime',
    'field',
    'field_group',
    'entity_reference_revisions',
    'entity_test',
    'link',
    'image',
    'node',
    'maxlength',
    'media',
    'oe_media',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_documents_field',
    'oe_content_reference_code_field',
    'oe_content_social_media_links_field',
    'oe_content_entity',
    'oe_content_call_tenders',
    'options',
    'rdf_skos',
    'system',
    'text',
    'typed_link',
    'user',
    'composite_reference',
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
    $this->installEntitySchema('entity_test');
    module_load_include('install', 'oe_content_documents_field');
    oe_content_documents_field_install(FALSE);
    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_reference_code_field',
      'oe_content_social_media_links_field',
      'oe_content_call_tenders',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

}
