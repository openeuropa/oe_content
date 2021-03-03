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
    'file_link',
    'oe_media',
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
      'oe_media',
    ]);

    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);

    module_load_include('install', 'oe_content');
    oe_content_install();
  }

  /**
   * Tests the label of the Person content type is generated automatically.
   */
  public function testPersonEntityLabel(): void {
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

  /**
   * Test that the entity person values are stored properly.
   */
  public function testPersonEntityValues(): void {

    $file = file_save_data(file_get_contents(drupal_get_path('module', 'oe_content') . '/tests/fixtures/sample.pdf'), 'public://sample.pdf');
    $file->save();
    $document_media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => 'document',
      'name' => 'Document title',
      'oe_media_file' => [
        'target_id' => $file->id(),
      ],
    ]);
    $document_media->save();

    $image = file_save_data(file_get_contents(drupal_get_path('module', 'oe_content') . '/tests/fixtures/example_1.jpeg'), 'public://example_1.jpeg');
    $image->save();
    $image_media = $this->entityTypeManager->getStorage('media')->create([
      'bundle' => 'image',
      'name' => 'Image media title',
      'oe_media_image' => [
        [
          'target_id' => $image->id(),
          'alt' => 'default alt',
        ],
      ],
    ]);
    $image_media->save();

    $organisation = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_organisation',
      'title' => 'Organisation title',
    ]);
    $organisation->save();

    $person = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'oe_person',
      'oe_person_first_name' => 'John',
      'oe_person_last_name' => 'Doe',
      'oe_person_type' => 'eu',
      'oe_departments' => [
        [
          'target_id' => 'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
        ],
      ],
      'oe_person_media' => [
        'target_id' => $image_media->id(),
      ],
      'oe_social_media_links' => [
        [
          'uri' => 'http://example.com',
          'title' => 'Social link',
          'link_type' => 'facebook',
        ],
      ],
      'oe_person_transparency_intro' => 'Transparency introduction text',
      'oe_person_transparency_links' => [
        [
          'uri' => 'http://example.com',
          'title' => 'Transparency link',
        ],
      ],
      'oe_person_biography_intro' => 'Biography introduction text',
      'oe_person_biography_timeline' => [
        [
          'title' => 'Timeline Title 1',
          'label' => 'Timeline Label 1',
          'value' => 'Timeline Value 1',
        ],
      ],
      'oe_person_cv' => [
        'target_id' => $document_media->id(),
      ],
      'oe_person_interests_intro' => 'Interests introduction text',
      'oe_person_interests_file' => [
        'target_id' => $document_media->id(),
      ],
      'oe_person_organisation' => [
        'target_id' => $organisation->id(),
        'revision_id' => $organisation->getRevisionId(),
      ],
    ]);
    $person->save();

    // Assert the values of a UE person are saved properly.
    $this->assertEquals('John Doe', $person->label());
    $this->assertEmpty($person->get('oe_person_organisation')->target_id);
    $this->assertEquals('http://publications.europa.eu/resource/authority/corporate-body/ABEC', $person->get('oe_departments')->target_id);
    $this->assertEquals($image_media->id(), $person->get('oe_person_media')->target_id);
    $this->assertEquals('http://example.com', $person->get('oe_social_media_links')->uri);
    $this->assertEquals('Transparency introduction text', $person->get('oe_person_transparency_intro')->value);
    $this->assertEquals('http://example.com', $person->get('oe_person_transparency_links')->uri);
    $this->assertEquals('Biography introduction text', $person->get('oe_person_biography_intro')->value);
    $this->assertEquals('Timeline Value 1', $person->get('oe_person_biography_timeline')->value);
    $this->assertEquals($document_media->id(), $person->get('oe_person_cv')->target_id);
    $this->assertEquals('Interests introduction text', $person->get('oe_person_interests_intro')->value);

    // Update the person to be non-eu and assert that the values
    // are updated properly.
    $person->set('oe_person_type', 'non_eu');
    $person->set('oe_person_organisation', [
      'target_id' => $organisation->id(),
      'revision_id' => $organisation->getRevisionId(),
    ]);
    $person->save();
    $this->assertEquals($organisation->id, $person->get('oe_person_organisation')->target_id);
    $this->assertEmpty($person->get('oe_departments')->target_id);
    $this->assertEmpty($person->get('oe_person_media')->target_id);
    $this->assertEmpty($person->get('oe_social_media_links')->uri);
    $this->assertEmpty($person->get('oe_person_transparency_intro')->value);
    $this->assertEmpty($person->get('oe_person_transparency_links')->uri);
    $this->assertEmpty($person->get('oe_person_biography_intro')->value);
    $this->assertEmpty($person->get('oe_person_biography_timeline')->value);
    $this->assertEmpty($person->get('oe_person_cv')->target_id);
    $this->assertEmpty($person->get('oe_person_interests_intro')->value);

  }

}
