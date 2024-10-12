<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content_organisation\Kernel;

use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;
use Drupal\node\Entity\Node;

/**
 * Test Organisation creation business logic.
 */
class OrganisationFieldTest extends SparqlKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'datetime',
    'field',
    'field_group',
    'entity_reference_revisions',
    'image',
    'inline_entity_form',
    'link',
    'node',
    'maxlength',
    'media',
    'oe_media',
    'oe_content',
    'oe_content_entity',
    'oe_content_entity_contact',
    'oe_content_organisation',
    'options',
    'rdf_skos',
    'system',
    'text',
    'user',
    'composite_reference',
    'description_list_field',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('oe_contact');
    $this->installConfig([
      'field',
      'node',
      'oe_content',
      'oe_content_organisation',
      'rdf_skos',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

  /**
   * Test that organisation fields are correctly saved.
   */
  public function testOrganisationFields(): void {
    $values = [
      'type' => 'oe_organisation',
      'title' => 'My node title',
      'oe_organisation_org_type' => 'eu',
      'oe_organisation_non_eu_org_type' => 'http://data.europa.eu/uxp/1051',
      'oe_organisation_eu_org' => 'http://publications.europa.eu/resource/authority/corporate-body/DIGIT',
    ];

    $node = Node::create($values);
    $node->save();

    // If the type is 'EU' only EU related organsiation fields are filled in.
    $this->assertTrue($node->get('oe_organisation_non_eu_org_type')->isEmpty());
    $this->assertEquals('Directorate-General for Digital Services', $node->get('oe_organisation_eu_org')->entity->label());
    $this->assertEquals('Directorate-general', $node->get('oe_organisation_eu_org_type')->entity->label());

    $node = Node::create([
      'oe_organisation_org_type' => 'non_eu',
    ] + $values);
    $node->save();

    // If the type is 'Non EU' EU related organsiation fields are not filled in.
    $this->assertTrue($node->get('oe_organisation_eu_org')->isEmpty());
    $this->assertTrue($node->get('oe_organisation_eu_org_type')->isEmpty());
    $this->assertEquals('foundation', $node->get('oe_organisation_non_eu_org_type')->entity->label());
  }

}
