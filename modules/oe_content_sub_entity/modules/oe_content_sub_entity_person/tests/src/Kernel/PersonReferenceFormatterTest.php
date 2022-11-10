<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_person\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests Persons reference formatter.
 */
class PersonReferenceFormatterTest extends EntityKernelTestBase {

  use NodeCreationTrait;
  use EntityReferenceRevisionTrait;
  use ContentTypeCreationTrait;
  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'link',
    'entity_reference_revisions',
    'sparql_entity_storage',
    'rdf_skos',
    'composite_reference',
    'media',
    'image',
    'text',
    'options',
    'datetime',
    'typed_link',
    'field_group',
    'maxlength',
    'description_list_field',
    'inline_entity_form',
    'oe_content_entity_contact',
    'oe_content_social_media_links_field',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_person',
    'oe_content_timeline_field',
    'oe_content_organisation',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_content_sub_entity_person',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->setUpCurrentUser(['uid' => 1]);
    $this->setUpSparql();

    $this->installEntitySchema('oe_person');
    $this->installEntitySchema('oe_document_reference');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');

    $this->installSchema('node', ['node_access']);

    $this->installConfig([
      'node',
      'filter',
      'media',
      'oe_content_social_media_links_field',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_person',
      'oe_content_organisation',
      'oe_content_sub_entity_person',
    ]);

    // Create a node type.
    $this->createContentType([
      'type' => 'test_node',
      'name' => 'Test node',
    ]);

    $this->createEntityReferenceRevisionField('node', 'test_node', 'oe_persons_reference', 'Persons', 'oe_person');

    \Drupal::moduleHandler()->loadInclude('oe_content', 'install');
    oe_content_install(FALSE);
    \Drupal::moduleHandler()->loadInclude('oe_content_sub_entity_person', 'install');
    oe_content_sub_entity_person_install(FALSE);
  }

  /**
   * Tests field formatter.
   */
  public function testFieldFormatter(): void {
    $person = $this->entityTypeManager->getStorage('oe_person')->create([
      'type' => 'oe_political_leader',
      'oe_skos_reference' => [
        'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0440FF',
        'http://publications.europa.eu/resource/authority/political-leader/COM_00006A044747',
      ],
    ]);
    $person->save();

    $node = $this->createNode([
      'type' => 'test_node',
      'oe_persons_reference' => [$person],
    ]);
    $node->save();
    $this->renderField($node);
    $links = $this->xpath('//main/div/div/div/span');
    $this->assertCount(2, $links);
    $this->assertEquals('Ursula von der Leyen', $links[0]->__toString());
    $this->assertEquals('Nicolas Schmit', $links[1]->__toString());

    // Change order of references but we should not see the change because,
    // the person sub-entity is referenced with it's revision id.
    $person->set('oe_skos_reference', [
      'http://publications.europa.eu/resource/authority/political-leader/COM_00006A044747',
      'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0440FF',
    ]);
    $person->save();
    $this->renderField($node);
    $links = $this->xpath('//main/div/div/div/span');
    $this->assertCount(2, $links);
    $this->assertEquals('Ursula von der Leyen', $links[0]->__toString());
    $this->assertEquals('Nicolas Schmit', $links[1]->__toString());

    // Add additional political leader with 1 skos reference.
    $person2 = $this->entityTypeManager->getStorage('oe_person')->create([
      'type' => 'oe_political_leader',
      'oe_skos_reference' => [
        'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0F334D',
      ],
    ]);
    $person2->save();
    $node->set('oe_persons_reference', [$person, $person2]);
    $node->save();
    $this->renderField($node);
    $links = $this->xpath('//main/div/div/div/span');
    $this->assertCount(3, $links);
    $this->assertEquals('Nicolas Schmit', $links[0]->__toString());
    $this->assertEquals('Ursula von der Leyen', $links[1]->__toString());
    $this->assertEquals('Didier Reynders', $links[2]->__toString());

    // Add Person author.
    $person_node = $this->createNode([
      'type' => 'oe_person',
      'oe_person_first_name' => 'John',
      'oe_person_last_name' => 'Doe',
    ]);
    $person_node->save();
    $person3 = $this->entityTypeManager->getStorage('oe_person')->create([
      'type' => 'oe_person',
      'oe_node_reference' => [$person_node],
    ]);
    $person3->save();
    $node->set('oe_persons_reference', [$person, $person2, $person3]);
    $node->save();
    $this->renderField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertCount(4, $links);
    $this->assertEquals('Nicolas Schmit', $links[0]->span->__toString());
    $this->assertEquals('Ursula von der Leyen', $links[1]->span->__toString());
    $this->assertEquals('Didier Reynders', $links[2]->span->__toString());
    $this->assertEquals('John Doe', $links[3]->a->__toString());

    // Update Person title and find update in rendered field.
    $person_node->set('oe_person_last_name', 'Doe II');
    $person_node->setNewRevision(FALSE);
    $person_node->save();
    $person3->save();

    // Need to ensure the new revision of the persons is assigned to the node.
    $node->set('oe_persons_reference', [$person, $person2, $person3]);
    $node->save();

    $this->renderField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertEquals('John Doe II', $links[3]->a->__toString());
  }

  /**
   * Helper method for rendering field using "Persons reference formatter".
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param array $settings
   *   Optional settings of field formatter.
   */
  protected function renderField(NodeInterface $node, array $settings = []): void {
    $render_array = $node->get('oe_persons_reference')->view([
      'type' => 'oe_content_sub_entity_person_reference_formatter',
      'settings' => $settings,
    ]);

    $this->render($render_array);
  }

}
