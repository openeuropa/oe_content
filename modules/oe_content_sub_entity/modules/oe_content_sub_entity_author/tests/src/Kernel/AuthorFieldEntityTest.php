<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_author\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\NodeInterface;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\oe_content\Traits\EntityReferenceRevisionTrait;
use Drupal\Tests\sparql_entity_storage\Traits\SparqlConnectionTrait;

/**
 * Tests Authors reference formatter for all author entity bundles.
 */
class AuthorFieldEntityTest extends EntityKernelTestBase {

  use NodeCreationTrait;
  use EntityReferenceRevisionTrait;
  use ContentTypeCreationTrait;
  use SparqlConnectionTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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
    'oe_content_sub_entity_author',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpCurrentUser(['uid' => 1]);
    $this->setUpSparql();

    $this->installEntitySchema('oe_author');
    $this->installEntitySchema('oe_document_reference');
    $this->installEntitySchema('node');
    $this->installEntitySchema('media');
    $this->installEntitySchema('oe_person_job');

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
      'oe_content_sub_entity_author',
    ]);

    // Create a node type.
    $this->createContentType([
      'type' => 'test_node',
      'name' => 'Test node',
    ]);

    $this->createEntityReferenceRevisionField('node', 'test_node', 'oe_authors', 'Authors', 'oe_author');

    \Drupal::moduleHandler()->loadInclude('oe_content', 'install');
    oe_content_install(FALSE);
    \Drupal::moduleHandler()->loadInclude('oe_content_sub_entity_author', 'install');
    oe_content_sub_entity_author_install(FALSE);
  }

  /**
   * Tests label of extra author entities.
   */
  public function testAuthorsField(): void {
    $author = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_corporate_body',
      'oe_skos_reference' => [
        'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
        'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
      ],
    ]);
    $author->save();

    $node = $this->createNode([
      'type' => 'test_node',
      'oe_authors' => [$author],
    ]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div/span');
    $this->assertCount(2, $links);
    $this->assertEquals('Audit Board of the European Communities', $links[0]->__toString());
    $this->assertEquals('Directorate-General for Budget', $links[1]->__toString());

    // Change order of references but we should not see the change because,
    // the author sub-entity is referenced with it's revision id.
    $author->set('oe_skos_reference', [
      'http://publications.europa.eu/resource/authority/corporate-body/BUDG',
      'http://publications.europa.eu/resource/authority/corporate-body/ABEC',
    ]);
    $author->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div/span');
    $this->assertCount(2, $links);
    $this->assertEquals('Audit Board of the European Communities', $links[0]->__toString());
    $this->assertEquals('Directorate-General for Budget', $links[1]->__toString());

    // Add additional Corporate body Author with 1 skos reference.
    $author2 = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_corporate_body',
      'oe_skos_reference' => [
        'http://publications.europa.eu/resource/authority/corporate-body/CLIMA',
      ],
    ]);
    $author2->save();
    $node->set('oe_authors', [$author, $author2]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div/span');
    $this->assertCount(3, $links);
    $this->assertEquals('Directorate-General for Budget', $links[0]->__toString());
    $this->assertEquals('Audit Board of the European Communities', $links[1]->__toString());
    $this->assertEquals('Directorate-General for Climate Action', $links[2]->__toString());

    // Add Person author.
    $person_node = $this->createNode([
      'type' => 'oe_person',
      'oe_person_first_name' => 'John',
      'oe_person_last_name' => 'Doe',
    ]);
    $person_node->save();
    $author3 = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_person',
      'oe_node_reference' => [
        $person_node->id(),
      ],
    ]);
    $author3->save();
    $node->set('oe_authors', [$author, $author2, $author3]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertCount(4, $links);
    $this->assertEquals('Directorate-General for Budget', $links[0]->span->__toString());
    $this->assertEquals('Audit Board of the European Communities', $links[1]->span->__toString());
    $this->assertEquals('Directorate-General for Climate Action', $links[2]->span->__toString());
    $this->assertEquals('John Doe', $links[3]->a->__toString());

    // Add Organisation author.
    $org_node1 = $this->createNode([
      'type' => 'oe_organisation',
      'title' => 'Org1',
    ]);
    $org_node1->save();
    $org_node2 = $this->createNode([
      'type' => 'oe_organisation',
      'title' => 'Org2',
    ]);
    $org_node2->save();

    $author4 = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_organisation',
      'oe_node_reference' => [
        $org_node1->id(),
        $org_node2->id(),

      ],
    ]);
    $author4->save();
    $node->set('oe_authors', [$author, $author2, $author3, $author4]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertCount(6, $links);
    $this->assertEquals('Directorate-General for Budget', $links[0]->span->__toString());
    $this->assertEquals('Audit Board of the European Communities', $links[1]->span->__toString());
    $this->assertEquals('Directorate-General for Climate Action', $links[2]->span->__toString());
    $this->assertEquals('John Doe', $links[3]->a->__toString());
    $this->assertEquals('Org1', $links[4]->a->__toString());
    $this->assertEquals('Org2', $links[5]->a->__toString());

    // Load all existing author entities and assert there are 4 entities.
    $authors = $this->entityTypeManager->getStorage('oe_author')->loadMultiple();
    $this->assertCount(4, $authors);
    // Delete the organisation nodes referenced by $author4 entity.
    $org_node1->delete();
    $org_node2->delete();
    // Load all existing author entities and assert there are only 3 now.
    $authors = $this->entityTypeManager->getStorage('oe_author')->loadMultiple();
    $this->assertCount(3, $authors);

    // Add Link author.
    $author5 = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_link',
      'oe_link' => [
          [
            'uri' => 'internal:/node/add',
            'title' => 'node add internal',
          ],
          [
            'uri' => 'entity:node/' . $person_node->id(),
            'title' => 'Link to John Doe person',
          ],
          [
            'uri' => 'http://example.com',
            'title' => 'external link',
          ],
      ],
    ]);
    $author5->save();
    $node->set('oe_authors', [$author, $author2, $author3, $author5]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertCount(7, $links);
    $this->assertEquals('Directorate-General for Budget', $links[0]->span->__toString());
    $this->assertEquals('Audit Board of the European Communities', $links[1]->span->__toString());
    $this->assertEquals('Directorate-General for Climate Action', $links[2]->span->__toString());

    $this->assertEquals('John Doe', $links[3]->a->__toString());
    $this->assertEquals('/node/2', $links[3]->a['href']);

    $this->assertEquals('node add internal', $links[4]->a->__toString());
    $this->assertEquals('/node/add', $links[4]->a['href']);

    $this->assertEquals('Link to John Doe person', $links[5]->a->__toString());
    $this->assertEquals('/node/2', $links[5]->a['href']);

    $this->assertEquals('external link', $links[6]->a->__toString());
    $this->assertEquals('http://example.com', $links[6]->a['href']);

    // Create a political leader author.
    $author6 = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_political_leader',
      'oe_skos_reference' => [
        'http://publications.europa.eu/resource/authority/political-leader/COM_00006A0F334D',
      ],
    ]);
    $author6->save();

    $node->set('oe_authors', [
      $author, $author2, $author3, $author5, $author6,
    ]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertCount(8, $links);
    $this->assertEquals('Didier Reynders', $links[7]->span->__toString());

    // Update Person title and find update in rendered field.
    $person_node->set('oe_person_last_name', 'Doe II');
    $person_node->setNewRevision(FALSE);
    $person_node->save();
    $author3->save();

    // We need to ensure the new revision of the author is assigned to the node.
    $node->set('oe_authors', [
      $author, $author2, $author3, $author5, $author6,
    ]);
    $node->save();

    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertEquals('John Doe II', $links[3]->a->__toString());

    // Update Link author.
    $author5->set('oe_link', [
      [
        'uri' => 'internal:/node/add',
        'title' => 'node add internal',
      ],
      [
        'uri' => 'http://example.com/updated',
        'title' => 'external link updated',
      ],
      [
        'uri' => 'entity:node/' . $person_node->id(),
        'title' => 'Link to John Doe person',
      ],
    ]);
    $author5->save();

    // We need to ensure the new revision of the author is assigned to the node.
    $node->set('oe_authors', [
      $author, $author2, $author3, $author5, $author6,
    ]);
    $node->save();
    $this->renderAuthorField($node);
    $links = $this->xpath('//main/div/div/div');
    $this->assertEquals('node add internal', $links[4]->a->__toString());
    $this->assertEquals('/node/add', (string) $links[4]->a['href']);

    $this->assertEquals('external link updated', $links[5]->a->__toString());
    $this->assertEquals('http://example.com/updated', (string) $links[5]->a['href']);

    $this->assertEquals('Link to John Doe person', $links[6]->a->__toString());
    $this->assertEquals('/node/2', (string) $links[6]->a['href']);

    // Load all existing author entities and assert there are 5.
    $authors = $this->entityTypeManager->getStorage('oe_author')->loadMultiple();
    $this->assertCount(5, $authors);
    // Delete the person node referenced by $author3 entity.
    $person_node->delete();
    // Load all existing author entities and assert there are only 4 now.
    $authors = $this->entityTypeManager->getStorage('oe_author')->loadMultiple();
    $this->assertCount(4, $authors);

    $node->set('oe_authors', [
      $author, $author2, $author5, $author6,
    ]);
    $node->save();
    $this->renderAuthorField($node, [
      'label_only' => TRUE,
    ]);
    $links = $this->xpath('//main//a');
    $this->assertCount(0, $links);

    $labels = $this->xpath('//main/div/div/div');

    $this->assertEquals('Directorate-General for Budget', $labels[0]->__toString());
    $this->assertEquals('Audit Board of the European Communities', $labels[1]->__toString());
    $this->assertEquals('Directorate-General for Climate Action', $labels[2]->__toString());
    $this->assertEquals('node add internal', $labels[3]->__toString());
    $this->assertEquals('external link updated', $labels[4]->__toString());
    $this->assertEquals('Link to John Doe person', $labels[5]->__toString());
    $this->assertEquals('Didier Reynders', $labels[6]->__toString());

    $this->renderAuthorField($node, [
      'label_only' => FALSE,
      'rel' => 'nofollow',
      'target' => '_blank',
    ]);
    $links = $this->xpath('//main//a');
    foreach ($links as $link) {
      $this->assertEquals('nofollow', (string) $link['rel']);
      $this->assertEquals('_blank', (string) $link['target']);
    }
  }

  /**
   * Helper method for rendering Author reference field.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param array $settings
   *   Optional settings of field formatter.
   */
  protected function renderAuthorField(NodeInterface $node, array $settings = []): void {
    $render_array = $node->get('oe_authors')->view([
      'type' => 'oe_content_authors_reference_formatter',
      'settings' => $settings,
    ]);

    $this->render($render_array);
  }

}
