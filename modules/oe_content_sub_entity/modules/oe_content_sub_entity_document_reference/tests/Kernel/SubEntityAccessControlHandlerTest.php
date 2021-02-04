<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_sub_entity_document_reference\Kernel;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests access handler.
 *
 * @coversDefaultClass \Drupal\oe_content_sub_entity\SubEntityAccessControlHandler
 */
class SubEntityAccessControlHandlerTest extends EntityKernelTestBase {

  use NodeCreationTrait;
  use ContentTypeCreationTrait;
  use EntityReferenceTestTrait;

  /**
   * The access control handler.
   *
   * @var \Drupal\oe_content_sub_entity\SubEntityAccessControlHandler
   */
  protected $accessControlHandler;

  /**
   * Sub entity to test access.
   *
   * @var \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   */
  protected $subEntity;

  /**
   * Entity storage for sub entity.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $subEntityStorage;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'entity_reference_revisions',
    'node',
    'oe_content_sub_entity',
    'oe_content_sub_entity_document_reference',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // @todo: better to create test sub entity type and avoid using
    // Document reference type.
    $this->installEntitySchema('oe_document_reference');
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->installSchema('node', ['node_access']);

    $this->accessControlHandler = $this->entityTypeManager->getAccessControlHandler('oe_document_reference');
    $this->subEntityStorage = $this->entityTypeManager->getStorage('oe_document_reference');

    // Create Page node type.
    $this->createContentType([
      'type' => 'page',
      'name' => 'Basic page',
    ]);

    // Create test bundle of Document reference entity.
    $type_storage = $this->container->get('entity_type.manager')->getStorage('oe_document_reference_type');
    $type_storage->create([
      'id' => 'test_bundle',
      'label' => 'Test bundle',
    ])->save();
    $this->createEntityReferenceField('oe_document_reference', 'test_bundle', 'field_reference', 'Node reference', 'node');

    // Create Article node type.
    $this->createContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $this->createEntityReferenceField('node', 'article', 'field_reference', 'Node reference 1', 'node');

    // Create the entity reference revision field to the document reference
    // entity in the Article node.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'field_sub_entity_reference',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'settings' => [
        'target_type' => 'oe_document_reference',
      ],
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'translatable' => FALSE,
    ]);
    $field->save();

    // Create chain of entities: Article node -> Sub entity -> Page node.
    $page_node = $this->createNode([
      'title' => 'Page node label. Child node.',
    ]);
    $document_reference = $this->subEntityStorage->create([
      'type' => 'test_bundle',
      'field_reference' => [$page_node],
      'status' => 1,
    ]);
    $document_reference->save();
    $article_node = $this->createNode([
      'title' => 'Article node label. Parent node.',
      'type' => 'article',
      'field_sub_entity_reference' => [$document_reference],
    ]);
    $article_node->save();

    // Save sub entity for further processing.
    $this->subEntity = $this->subEntityStorage->load($document_reference->id());
  }

  /**
   * Tests create access.
   *
   * @covers ::checkCreateAccess
   *
   * @dataProvider createAccessTestCases
   */
  public function testCreateAccess($request_format, $expected_result) {
    $request = new Request();
    $request->setRequestFormat($request_format);
    $this->container->get('request_stack')->push($request);
    $result = $this->accessControlHandler->createAccess(NULL, NULL, [], TRUE);
    $this->assertEquals($expected_result, $result);
  }

  /**
   * Test cases for ::testCreateAccess.
   *
   * @return array
   *   Expected results.
   */
  public function createAccessTestCases(): array {
    $container = new ContainerBuilder();
    $cache_contexts_manager = $this->prophesize(CacheContextsManager::class);
    $cache_contexts_manager->assertValidTokens()->willReturn(TRUE);
    $cache_contexts_manager->reveal();
    $container->set('cache_contexts_manager', $cache_contexts_manager);
    \Drupal::setContainer($container);

    return [
      'Allowed HTML request format' => [
        'html',
        AccessResult::allowed()->addCacheContexts(['request_format']),
      ],
      'Forbidden other formats' => [
        'json',
        AccessResult::neutral()->addCacheContexts(['request_format']),
      ],
    ];
  }

  /**
   * Ensures sub entity access is properly working.
   *
   * @covers ::checkAccess
   */
  public function testAccess() {
    $scenarios = $this->accessTestScenarios();
    // Run through the scenarios and assert the expectations.
    foreach ($scenarios as $scenario => $test_data) {
      // Update the published status based on the scenario.
      $this->subEntity->setPublished($test_data['status']);
      $this->subEntity->save();
      $user = $this->drupalCreateUser($test_data['permissions']);
      $this->assertAccessResult(
        $test_data['expected_result'],
        $this->accessControlHandler->access($this->subEntity, $test_data['operation'], $user, TRUE),
        sprintf('Failed asserting access for "%s" scenario.', $scenario)
      );
    }
  }

  /**
   * Asserts entity access correctly grants or denies access.
   *
   * @param \Drupal\Core\Access\AccessResultInterface $expected
   *   The expected result.
   * @param \Drupal\Core\Access\AccessResultInterface $actual
   *   The actual result.
   * @param string $message
   *   Failure message.
   */
  protected function assertAccessResult(AccessResultInterface $expected, AccessResultInterface $actual, string $message = ''): void {
    $this->assertEquals($expected->isAllowed(), $actual->isAllowed(), $message);
    $this->assertEquals($expected->isForbidden(), $actual->isForbidden(), $message);
    $this->assertEquals($expected->isNeutral(), $actual->isNeutral(), $message);

    $this->assertEquals($expected->getCacheMaxAge(), $actual->getCacheMaxAge(), $message);
    $cache_types = [
      'getCacheTags',
      'getCacheContexts',
    ];
    foreach ($cache_types as $type) {
      $expected_cache_data = $expected->{$type}();
      $actual_cache_data = $actual->{$type}();
      sort($expected_cache_data);
      sort($actual_cache_data);
      $this->assertEquals($expected_cache_data, $actual_cache_data, $message);
    }
  }

  /**
   * Provides test scenarios for testCreateAccess().
   *
   * @return array
   *   The data sets to test.
   */
  protected function accessTestScenarios(): array {
    return [
      'user without permissions / view / published entity' => [
        'permissions' => [],
        'operation' => 'view',
        'expected_result' => AccessResult::forbidden()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user without permissions / view / unpublished entity' => [
        'permissions' => [],
        'operation' => 'view',
        'expected_result' => AccessResult::forbidden()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'user with access content / view / published entity' => [
        'permissions' => ['access content'],
        'operation' => 'view',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions'])->addCacheTags(['node:2']),
        'status' => 1,
      ],
      'user with access content / view / unpublished entity' => [
        'permissions' => ['access content'],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral(),
        'status' => 0,
      ],
      'user with access content view unpublished sub entities / view / unpublished entity' => [
        'permissions' => ['access content', 'view unpublished sub entities'],
        'operation' => 'view',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions'])->addCacheTags(['node:2']),
        'status' => 0,
      ],
      'user with view unpublished sub entities / view / unpublished entity' => [
        'permissions' => ['view unpublished sub entities'],
        'operation' => 'view',
        'expected_result' => AccessResult::forbidden()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'user with access content / update' => [
        'permissions' => ['access content'],
        'operation' => 'update',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with access content / delete' => [
        'permissions' => ['access content'],
        'operation' => 'delete',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with bypass node access / update' => [
        'permissions' => ['bypass node access'],
        'operation' => 'update',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with bypass node access / delete' => [
        'permissions' => ['bypass node access'],
        'operation' => 'delete',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
    ];
  }

}
