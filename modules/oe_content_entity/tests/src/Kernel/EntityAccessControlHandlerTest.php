<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_entity\Kernel;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Test the corporate entity access control handler.
 */
class EntityAccessControlHandlerTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'oe_content_entity',
    'oe_content_entity_test',
    'system',
    'user',
  ];

  /**
   * The access control handler.
   *
   * @var \Drupal\oe_content_entity\EntityAccessControlHandler
   */
  protected $accessControlHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installEntitySchema('oe_corporate');
    $this->installConfig('oe_content_entity');
    $this->installConfig('oe_content_entity_test');

    $this->accessControlHandler = $this->container->get('entity_type.manager')->getAccessControlHandler('oe_corporate');

    // Create a UID 1 user to be able to create test users with particular
    // permissions in the tests.
    $this->drupalCreateUser();

    // Create a test bundles.
    $type_storage = $this->container->get('entity_type.manager')->getStorage('oe_corporate_type');
    $type_storage->create([
      'id' => 'test_bundle',
      'label' => 'Test bundle',
    ])->save();
    $type_storage = $this->container->get('entity_type.manager')->getStorage('oe_corporate_type');
    $type_storage->create([
      'id' => 'test',
      'label' => 'Contact test',
    ])->save();
  }

  /**
   * Ensures corporate entity access is properly working.
   */
  public function testAccess() {
    $scenarios = $this->accessDataProvider();
    $contact_storage = $this->container->get('entity_type.manager')->getStorage('oe_corporate');
    $values = [
      'bundle' => 'test_bundle',
      'name' => 'My corporate entity',
    ];
    foreach ($scenarios as $scenario => $test_data) {
      $values['status'] = $test_data['status'];
      /** @var \Drupal\oe_content_entity\Entity\EntityTypeBaseInterface $entity */
      // Create an entity.
      $entity = $contact_storage->create($values);
      $entity->save();

      $user = $this->drupalCreateUser($test_data['permissions']);
      $this->assertAccessResult(
        $test_data['expected_result'],
        $this->accessControlHandler->access($entity, $test_data['operation'], $user, TRUE),
        sprintf('Failed asserting access for "%s" scenario.', $scenario)
      );
    }
  }

  /**
   * Ensures corporate entity create access is properly working.
   */
  public function testCreateAccess() {
    $scenarios = $this->createAccessDataProvider();
    foreach ($scenarios as $scenario => $test_data) {
      $user = $this->drupalCreateUser($test_data['permissions']);
      $this->assertAccessResult(
        $test_data['expected_result'],
        $this->accessControlHandler->createAccess('test_bundle', $user, [], TRUE),
        sprintf('Failed asserting access for "%s" scenario.', $scenario)
      );
    }
  }

  /**
   * Data provider for testAccess().
   *
   * This method is not declared as a real PHPUnit data provider to speed up
   * test execution.
   *
   * @return array
   *   The data sets to test.
   */
  protected function accessDataProvider() {
    return [
      'user without permissions / published entity' => [
        'permissions' => [],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user without permissions / unpublished entity' => [
        'permissions' => [],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'admin view / published entity' => [
        'permissions' => ['manage corporate content entities'],
        'operation' => 'view',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'admin view / unpublished entity' => [
        'permissions' => ['manage corporate content entities'],
        'operation' => 'view',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'admin update' => [
        'permissions' => ['manage corporate content entities'],
        'operation' => 'update',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'admin delete' => [
        'permissions' => ['manage corporate content entities'],
        'operation' => 'delete',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with view published access / published entity' => [
        'permissions' => ['view published oe_corporate'],
        'operation' => 'view',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with view published access / unpublished entity' => [
        'permissions' => ['view published oe_corporate'],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'user with view unpublished access / unpublished entity' => [
        'permissions' => ['view unpublished oe_corporate'],
        'operation' => 'view',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'user with view unpublished access / published entity' => [
        'permissions' => ['view unpublished oe_corporate'],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with create, update, delete access / published entity' => [
        'permissions' => [
          'create test_bundle corporate entity',
          'edit test_bundle corporate entity',
          'delete test_bundle corporate entity',
        ],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with create, update, delete access / unpublished entity' => [
        'permissions' => [
          'create test_bundle corporate entity',
          'edit test_bundle corporate entity',
          'delete test_bundle corporate entity',
        ],
        'operation' => 'view',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 0,
      ],
      'user with update access' => [
        'permissions' => ['edit test_bundle corporate entity'],
        'operation' => 'update',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with update access on different bundle' => [
        'permissions' => ['edit test corporate entity'],
        'operation' => 'update',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with create, view, delete access' => [
        'permissions' => [
          'create test_bundle corporate entity',
          'view published oe_corporate',
          'view unpublished oe_corporate',
          'delete test_bundle corporate entity',
        ],
        'operation' => 'update',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with delete access' => [
        'permissions' => ['delete test_bundle corporate entity'],
        'operation' => 'delete',
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with delete access on different bundle' => [
        'permissions' => ['delete test corporate entity'],
        'operation' => 'delete',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
      'user with create, view, update access' => [
        'permissions' => [
          'create test_bundle corporate entity',
          'view published oe_corporate',
          'view unpublished oe_corporate',
          'edit test_bundle corporate entity',
        ],
        'operation' => 'delete',
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
        'status' => 1,
      ],
    ];
  }

  /**
   * Data provider for testCreateAccess().
   *
   * This method is not declared as a real PHPUnit data provider to speed up
   * test execution.
   *
   * @return array
   *   The data sets to test.
   */
  protected function createAccessDataProvider() {
    return [
      'user without permissions' => [
        'permissions' => [],
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
      ],
      'admin' => [
        'permissions' => ['manage corporate content entities'],
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
      ],
      'user with view access' => [
        'permissions' => ['view published oe_corporate'],
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
      ],
      'user with view, update and delete access' => [
        'permissions' => [
          'view published oe_corporate',
          'view unpublished oe_corporate',
          'edit test_bundle corporate entity',
          'delete test_bundle corporate entity',
        ],
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
      ],
      'user with create access' => [
        'permissions' => ['create test_bundle corporate entity'],
        'expected_result' => AccessResult::allowed()->addCacheContexts(['user.permissions']),
      ],
      'user with create access on different bundle' => [
        'permissions' => ['create test corporate entity'],
        'expected_result' => AccessResult::neutral()->addCacheContexts(['user.permissions']),
      ],
    ];
  }

  /**
   * Asserts corporate entity access correctly grants or denies access.
   *
   * @param \Drupal\Core\Access\AccessResultInterface $expected
   *   The expected result.
   * @param \Drupal\Core\Access\AccessResultInterface $actual
   *   The actual result.
   * @param string $message
   *   Failure message.
   */
  protected function assertAccessResult(AccessResultInterface $expected, AccessResultInterface $actual, string $message = '') {
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

}
