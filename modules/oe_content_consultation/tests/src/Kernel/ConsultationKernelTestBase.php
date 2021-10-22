<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_consultation\Kernel;

use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Base test class for "Consultation" content type kernel tests.
 */
abstract class ConsultationKernelTestBase extends SparqlKernelTestBase {

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
    'node',
    'maxlength',
    'media',
    'image',
    'oe_media',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_content_consultation',
    'rdf_skos',
    'composite_reference',
    'user',
    'system',
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
    $this->installEntitySchema('oe_document_reference');
    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_consultation',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

}
