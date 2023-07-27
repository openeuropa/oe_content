<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_person\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the PersonNodeUpdater service.
 */
class PersonNodeUpdaterTest extends KernelTestBase {

  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'link',
    'entity_reference_revisions',
    'sparql_entity_storage',
    'user',
    'rdf_skos',
    'composite_reference',
    'media',
    'image',
    'field',
    'filter',
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
    'oe_content_entity_contact',
    'oe_content_organisation',
    'oe_content_timeline_field',
    'oe_content_person',
    'oe_content_person_reference',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
    'oe_content_sub_entity_person',
    'oe_content_person_sub_entity_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installEntitySchema('oe_person');
    $this->installEntitySchema('node');

    $this->installSchema('node', ['node_access']);

    $this->installConfig([
      'node',
      'filter',
      'media',
      'oe_content_social_media_links_field',
      'oe_content',
      'oe_content_departments_field',
      'oe_content_person',
      'oe_content_person_reference',
      'oe_content_sub_entity_person',
    ]);
    \Drupal::moduleHandler()->loadInclude('oe_content', 'install');
    oe_content_install(FALSE);
    \Drupal::moduleHandler()->loadInclude('oe_content_person_sub_entity_reference', 'install');
    oe_content_person_sub_entity_reference_install(FALSE);

    // Create a node type for the test.
    $this->createContentType(['type' => 'page']);

    // Create an entity reference field to Person node.
    FieldConfig::create([
      'field_name' => 'oe_persons',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'Related person',
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => ['page'],
          'sort' => ['field' => 'title', 'direction' => 'DESC'],
        ],
      ],
    ])->save();

    // Create an entity reference revision field to Person sub entity.
    FieldConfig::create([
      'field_name' => 'oe_persons_reference',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'Related person',
      'settings' => [
        'handler' => 'default',
        'handler_settings' => [
          'target_bundles' => ['page'],
          'sort' => ['field' => 'title', 'direction' => 'DESC'],
        ],
      ],
    ])->save();
  }

  /**
   * Tests update of person values to sub-entities by the service.
   */
  public function testNodeUpdate(): void {
    // Create test Person nodes.
    $person_nodes = [];
    for ($i = 0; $i < 3; $i++) {
      $person_node = $this->createNode([
        'title' => "Person $i",
        'type' => 'oe_person',
      ]);
      $person_node->save();
      $person_nodes[] = $person_node;
    }

    // Create a page node with multiple revisions.
    $entity_type_manager = $this->container->get('entity_type.manager');
    $node_storage = $entity_type_manager->getStorage('node');
    /** @var \Drupal\node\Entity\Node $node */
    $node = $node_storage->create([
      'type' => 'page',
      'title' => 'Test Page node',
      'oe_persons' => [$person_nodes[0]],
      'uid' => 0,
      'status' => 1,
    ]);
    $node->save();
    $node->set('oe_persons', [
      $person_nodes[0],
      $person_nodes[1],
    ]);
    $node->setNewRevision();
    $node->save();
    $node->set('oe_persons', [
      $person_nodes[0],
      $person_nodes[1],
      $person_nodes[2],
    ]);
    $node->setNewRevision();
    $node->save();
    $node->set('oe_persons', []);
    $node->setNewRevision();
    $node->save();
    $node->set('oe_persons', [$person_nodes[1]]);
    $node->setNewRevision();
    $node->save();

    // Assert the node revision id.
    $this->assertEquals(8, $node->getRevisionId());

    // Assert there are no person sub-entities.
    $person_storage = $entity_type_manager->getStorage('oe_person');
    $this->assertEmpty($person_storage->loadMultiple());

    // Invoke the service and update the node.
    $update = $this->container->get('oe_content_sub_entity_person.node_updater');
    $update->updateNode($node);

    // Reload the node.
    $node_storage->resetCache();
    $node = $node_storage->load($node->id());

    // Assert the node revision id is the same and there are no more revisions
    // created after update.
    $this->assertEquals(8, $node->getRevisionId());

    // Assert there is only one person sub-entity created after the update.
    $this->assertCount(1, $person_storage->loadMultiple());

    // Load up the revisions of the node and compare the field values against
    // the person sub-entity values.
    $revision_ids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->allRevisions()
      ->condition('nid', $node->id())
      ->sort('vid')
      ->execute();

    $revisions = $node_storage->loadMultipleRevisions(array_keys($revision_ids));
    $this->assertEquals(5, count($revisions));

    foreach ($revisions as $revision) {
      if ($revision->get('oe_persons')->isEmpty()) {
        // If the oe_persons field is empty the 'oe_persons_reference' field
        // will be empty as well and the sub-entity doesn't have revision in it.
        $this->assertTrue($revision->get('oe_persons_reference')->isEmpty());
        continue;
      }
      $oe_person = $revision->get('oe_persons')->getValue();
      $this->assertNotEmpty($oe_person);
      $persons = $revision->get('oe_persons_reference')->entity;
      $this->assertSame($oe_person, $persons->get('oe_node_reference')->getValue());
    }
  }

}
