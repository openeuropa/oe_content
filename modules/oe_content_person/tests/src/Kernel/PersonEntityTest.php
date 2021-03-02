<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_person\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\rdf_entity\Traits\RdfDatabaseConnectionTrait;

/**
 * Tests the Person content type.
 */
class PersonEntityTest extends EntityKernelTestBase {

  use RdfDatabaseConnectionTrait;

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
    'rdf_entity',
    'rdf_skos',
    'image',
    'inline_entity_form',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_entity_contact',
    'oe_content_person',
    'oe_content_social_media_links_field',
    'oe_content_sub_entity_document_reference',
    'oe_content_timeline_field',
    'typed_link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
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
    ]);

    $this->installSchema('node', ['node_access']);

    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Tests the label of the Person content type is generated automatically.
   */
  public function testAutoLabel() {
    $person = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_person',
    ]);
    $person->save();
    // We are missing the first and last name.
    $this->assertEquals(' ', $person->label());

    // Set a first and last name.
    $person->set('oe_person_first_name', 'Jacques');
    $person->set('oe_person_last_name', 'Delors');
    $person->save();

    $this->assertEquals('Jacques Delors', $person->label());

    // Set a displayed name.
    $person->set('oe_person_displayed_name', 'Delors Jacques');
    $person->save();

    $this->assertEquals('Delors Jacques', $person->label());

    // Remove the displayed name.
    $person->set('oe_person_displayed_name', NULL);
    $person->save();
    $this->assertEquals('Jacques Delors', $person->label());
  }

}
