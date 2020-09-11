<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_tender\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Base test class for tender content type kernel tests.
 */
abstract class TenderKernelTestBase extends RdfKernelTestBase {

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
    'oe_content_departments_field',
    'oe_content_publication',
    'oe_content_reference_code_field',
    'oe_content_social_media_links_field',
    'oe_content_entity',
    'oe_content_tender',
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
    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_publication',
      'oe_content_reference_code_field',
      'oe_content_social_media_links_field',
      'oe_content_tender',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

}
