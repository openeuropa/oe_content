<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_persistent\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\RoleInterface;

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
  public static $modules = [
    'path',
    'node',
    'user',
    'system',
    'language',
    'content_translation',
    'oe_content_persistent',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('configurable_language');
    $this->installSchema('node', ['node_access']);
    \Drupal::service('router.builder')->rebuild();

    $node_type = NodeType::create(['type' => 'page']);
    $node_type->save();

    $this->installConfig(['language']);
    $this->installConfig(['user']);

    ConfigurableLanguage::create(['id' => 'fr'])->save();

    $config = $this->config('language.negotiation');
    $config->set('url.prefixes', ['en' => '', 'fr' => 'fr'])
      ->save();

    user_role_grant_permissions(RoleInterface::ANONYMOUS_ID, ['access content']);

    \Drupal::service('kernel')->rebuildContainer();

  }

  /**
   * Test return of ContentUuidResolver service.
   */
  public function testContentUuidResolver(): void {

    /** @var \Drupal\oe_content_persistent\ContentUuidResolver $uuid_resolver */
    $uuid_resolver = \Drupal::service('oe_content_persistent.resolver');

    // @TODO Investigate impact.
    /** @var \Drupal\Core\PathProcessor\PathProcessorManager $path_processor_manager */
    $path_processor_manager = \Drupal::service('path_processor_manager');
    /** @var \Drupal\Core\PathProcessor\PathProcessorAlias $path_processor */
    $path_processor = \Drupal::service('path_processor_alias');
    $path_processor_manager->addOutbound($path_processor);
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $node = Node::create([
      'title' => 'Testing create()',
      'type' => 'page',
      'path' => ['alias' => '/foo'],
      'status' => TRUE,
    ]);
    $node->save();

    $node_storage->resetCache();
    /* @var \Drupal\Core\Entity\TranslatableInterface $entity */
    $entity = $uuid_resolver->getEntityByUuid($node->uuid());
    $this->assertEquals('/foo', $entity->toUrl()->toString());

    $uuid_resolver->resetStaticCache();
    $node = $node_storage->load($node->id());

    $node->get('path')->alias = '/newalias';
    $node->save();
    $entity = $uuid_resolver->getEntityByUuid($node->uuid());
    $this->assertEquals('/newalias', $entity->toUrl()->toString());

    $uuid_resolver->resetStaticCache();
    $node = $node_storage->load($node->id());

    $node->path->alias = '';
    $node->save();
    $entity = $uuid_resolver->getEntityByUuid($node->uuid());
    $this->assertEquals('/node/' . $node->id(), $entity->toUrl()->toString());

    // Add a translation, verify it is being saved as expected.
    $uuid_resolver->resetStaticCache();
    $translation = $node->addTranslation('fr', $node->toArray());
    $translation->get('path')->alias = '/petitbar';
    $translation->save();

    $node = $node_storage->load($node->id());
    $node->path->alias = '/original_en';
    $node->save();

    $entity = $uuid_resolver->getEntityByUuid($node->uuid(), 'fr');
    $this->assertEquals('/fr/petitbar', $entity->toUrl()->toString());

    // Check original language.
    $uuid_resolver->resetStaticCache();
    $entity = $uuid_resolver->getEntityByUuid($node->uuid(), 'en');
    $this->assertEquals('/original_en', $entity->toUrl()->toString());

    // Update a translation, verify it is being saved as expected.
    $uuid_resolver->resetStaticCache();
    $translation = $node->getTranslation('fr');
    $translation->get('path')->alias = '/petitfoo';
    $translation->save();

    $entity = $uuid_resolver->getEntityByUuid($node->uuid(), 'fr');
    $this->assertEquals('/fr/petitfoo', $entity->toUrl()->toString());

    $entity = $uuid_resolver->getEntityByUuid($this->randomString(), 'fr');
    $this->assertEquals(NULL, $entity);
  }

}
