<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_author\Kernel;

use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\sparql_entity_storage\Kernel\SparqlKernelTestBase;

/**
 * Tests the AuthorSkosUpdater service.
 */
class AuthorSkosUpdaterTest extends SparqlKernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'link',
    'entity_reference_revisions',
    'sparql_entity_storage',
    'user',
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
    'system',
    'description_list_field',
    'inline_entity_form',
    'oe_content_social_media_links_field',
    'oe_content',
    'oe_content_departments_field',
    'oe_content_timeline_field',
    'oe_content_page',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_content_sub_entity_author',
    'oe_content_sub_entity_author_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('oe_author');
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
      'oe_content_page',
      'oe_content_departments_field',
      'oe_content_sub_entity_author',
      'oe_content_sub_entity_author_test',
    ]);
    module_load_include('install', 'oe_content');
    oe_content_install(FALSE);
  }

  /**
   * Tests update of author values to sub-entities by the service.
   */
  public function testNodeUpdate(): void {
    // Create a page node with multiple revisions.
    $entity_type_manager = $this->container->get('entity_type.manager');
    $node_storage = $entity_type_manager->getStorage('node');
    /** @var \Drupal\node\Entity\Node $node */
    $node = $node_storage->create([
      'type' => 'oe_page',
      'title' => 'Test Page node',
      'body' => 'Body text',
      'oe_teaser' => 'Teaser text',
      'oe_author' => [
        'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      ],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $node->set('oe_author', [
      'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
    ]);
    $node->setNewRevision();
    $node->save();
    $node->set('oe_author', [
      'http://publications.europa.eu/resource/authority/corporate-body/COMMU',
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
      'http://publications.europa.eu/resource/authority/corporate-body/AASM',
    ]);
    $node->setNewRevision();
    $node->save();
    $node->set('oe_author', []);
    $node->setNewRevision();
    $node->save();
    $node->set('oe_author', [
      'http://publications.europa.eu/resource/authority/corporate-body/ACM',
    ]);
    $node->setNewRevision();
    $node->save();

    // Assert the node revision id.
    $this->assertEquals(5, $node->getRevisionId());

    // Assert there are no author sub-entities.
    $author_storage = $entity_type_manager->getStorage('oe_author');
    $this->assertEmpty($author_storage->loadMultiple());

    // Invoke the service and update the node.
    $update = $this->container->get('oe_content_sub_entity_author.skos_updater');
    $update->updateNode($node);

    // Reload the node.
    $node_storage->resetCache();
    $node = $node_storage->load($node->id());

    // Assert the node revision id is the same and there are no more revisions
    // created after update.
    $this->assertEquals(5, $node->getRevisionId());

    // Assert there is only one author sub-entity created after the update.
    $this->assertCount(1, $author_storage->loadMultiple());

    // Load up the revisions of the node and compare the field values against
    // the author sub-entity values.
    $revision_ids = $node_storage->getQuery()
      ->allRevisions()
      ->condition('nid', $node->id())
      ->sort('vid')
      ->execute();

    $revisions = $node_storage->loadMultiple(array_keys($revision_ids));

    foreach ($revisions as $revision) {
      if ($revision->get('oe_author')->isEmpty()) {
        // If the oe_author field is empty the 'oe_authors' field will be empty
        // as well and the sub-entity doesn't have revision for it.
        $this->assertTrue($revision->get('oe_authors')->isEmpty());
        continue;
      }
      $oe_author = $revision->get('oe_author')->getValue();
      $authors = $revision->get('oe_authors')->entity;
      $this->assertSame($oe_author, $authors->get('oe_skos_reference')->getValue());
    }
  }

}
