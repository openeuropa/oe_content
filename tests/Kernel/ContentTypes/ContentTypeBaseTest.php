<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Kernel\ContentTypes;

use Drupal\Tests\rdf_entity\Kernel\RdfKernelTestBase;

/**
 * Base test class for the content type tests.
 */
class ContentTypeBaseTest extends RdfKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'link',
    'node',
    'oe_content',
    'rdf_skos',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('user', 'users_data');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installConfig(['oe_content', 'field', 'node']);
    module_load_include('install', 'oe_content');
    oe_content_install();
  }

}
