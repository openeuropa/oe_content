<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_event\Kernel;

use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Base test class for event content type kernel tests.
 */
abstract class EventKernelTestBase extends SparqlKernelTestBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'field_group',
    'datetime',
    'datetime_range',
    'datetime_range_timezone',
    'entity_reference_revisions',
    'entity_test',
    'link',
    'image',
    'inline_entity_form',
    'node',
    'maxlength',
    'media',
    'oe_media',
    'oe_content',
    'oe_content_social_media_links_field',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_entity_organisation',
    'oe_content_entity_venue',
    'oe_content_event',
    'options',
    'rdf_skos',
    'system',
    'text',
    'typed_link',
    'user',
    'composite_reference',
    'oe_content_event_event_programme',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->installSchema('user', 'users_data');
    $this->installSchema('node', 'node_access');
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('oe_contact');
    $this->installEntitySchema('oe_organisation');
    $this->installEntitySchema('oe_venue');
    $this->installEntitySchema('oe_event_programme');
    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_social_media_links_field',
      'oe_content_event',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

}
