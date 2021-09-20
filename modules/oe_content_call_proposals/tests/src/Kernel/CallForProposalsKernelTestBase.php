<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_call_proposals\Kernel;

use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Base test class for "Call for proposals" content type kernel tests.
 */
abstract class CallForProposalsKernelTestBase extends SparqlKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'datetime',
    'field',
    'field_group',
    'entity_reference_revisions',
    'entity_test',
    'inline_entity_form',
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
    'oe_content_entity_contact',
    'oe_content_call_proposals',
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
      'oe_content_call_proposals',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

}
