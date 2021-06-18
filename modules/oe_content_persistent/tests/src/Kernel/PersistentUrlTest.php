<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests Persistent url related controller and service.
 *
 * @group path
 */
class PersistentUrlTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'path',
    'node',
    'user',
    'system',
    'language',
    'content_translation',
    'oe_content_persistent',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('configurable_language');
    $this->installEntitySchema('path_alias');
    $this->installSchema('node', ['node_access']);

    $this->installConfig(['language']);
    $this->installConfig(['user']);
    $this->installConfig(['oe_content_persistent']);
    ConfigurableLanguage::create(['id' => 'fr'])->save();

    $node_type = NodeType::create(['type' => 'page']);
    $node_type->save();
  }

  /**
   * Test return of ContentUuidResolver service.
   */
  public function testContentUuidResolver(): void {

    /** @var \Drupal\oe_content_persistent\ContentUuidResolver $uuid_resolver */
    $uuid_resolver = $this->container->get('oe_content_persistent.uuid_resolver');

    $node = Node::create([
      'title' => 'Testing create()',
      'type' => 'page',
    ]);
    $node->save();

    // Make sure that we could get correct entity by uuid with correct language.
    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $uuid_resolver->getEntityByUuid($node->uuid());
    $this->assertEquals($node->uuid(), $entity->uuid());
    $this->assertEquals($node->language()->getId(), $entity->language()->getId());

    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $uuid_resolver->getEntityByUuid($node->uuid(), 'en');
    $this->assertEquals($node->uuid(), $entity->uuid());
    $this->assertEquals($node->language()->getId(), $entity->language()->getId());

    // Add a translation and verify that we get correct entity from service.
    $translation = $node->addTranslation('fr', $node->toArray());
    $translation->save();

    /* @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $uuid_resolver->getEntityByUuid($node->uuid(), 'fr');
    $this->assertEquals($translation->uuid(), $entity->uuid());
    $this->assertEquals($translation->language()->getId(), $entity->language()->getId());

    // Check try to get not existing entity.
    $entity = $uuid_resolver->getEntityByUuid($this->container->get('uuid')->generate());
    $this->assertEquals(NULL, $entity);
  }

}
