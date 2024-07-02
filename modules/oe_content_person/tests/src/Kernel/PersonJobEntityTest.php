<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_person\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests Person job entities.
 */
class PersonJobEntityTest extends EntityKernelTestBase {

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
    'oe_content_social_media_links_field',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_content_timeline_field',
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
      'oe_person_job',
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
    ]);

    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

  /**
   * Tests label of Person job entity default bundle.
   */
  public function testDefaulPersonJobLabel() {
    // Show bundle label by default.
    $person_job = $this->entityTypeManager->getStorage('oe_person_job')->create([
      'type' => 'oe_default',
    ]);
    $person_job->save();
    $this->assertEquals('Default', $person_job->label());

    // Show role as a label if they are defined.
    $person_job->set('oe_role_name', 'Role name label')->save();
    $this->assertEquals('Role name label', $person_job->label());

    $person_job->set('oe_role_reference', ['http://publications.europa.eu/resource/authority/corporate-body/APEC'])->save();
    $this->assertEquals('Asia-Pacific Economic Cooperation', $person_job->label());

    $person_job->set('oe_acting', TRUE)->save();
    $this->assertEquals('(Acting) Asia-Pacific Economic Cooperation', $person_job->label());
  }

}
