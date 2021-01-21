<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_consultation\Kernel;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Base test class for "Consultation" content type kernel tests.
 */
abstract class ConsultationKernelTestBase extends RdfKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field_group',
    'entity_reference_revisions',
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
    'oe_content_consultation',
    'rdf_skos',
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
    $this->installEntitySchema('oe_contact');
    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_consultation',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

}
