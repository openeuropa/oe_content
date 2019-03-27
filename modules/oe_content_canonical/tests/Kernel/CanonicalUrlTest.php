<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_canonical\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\oe_content_canonical\Controller\CanonicalUrlController;
use Drupal\user\RoleInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Route;

/**
 * Tests loading and storing data using PathItem.
 *
 * @group path
 */
class CanonicalUrlTest extends KernelTestBase {

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
    'filter',
    'language',
    'content_translation',
    'oe_content_canonical',
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

    $this->installConfig(['filter']);
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

    /** @var \Drupal\oe_content_canonical\ContentUuidResolver $uuid_resolver */
    $uuid_resolver = \Drupal::service('oe_content_canonical.resolver');

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $node = Node::create([
      'title' => 'Testing create()',
      'type' => 'page',
      'path' => ['alias' => '/foo'],
    ]);

    $node->save();

    $alias_of_node = $uuid_resolver->getAliasByUuid($node->uuid());

    $this->assertEquals('/foo', $alias_of_node);

    $uuid_resolver->resetStaticCache();
    $node = $node_storage->load($node->id());

    $node->path->alias = '/newalias';
    $node->save();
    $path_of_node = $uuid_resolver->getAliasByUuid($node->uuid());
    $this->assertEquals('/newalias', $path_of_node);

    $uuid_resolver->resetStaticCache();
    $node = $node_storage->load($node->id());

    $node->path->alias = '';
    $node->save();
    $path_of_node = $uuid_resolver->getAliasByUuid($node->uuid());
    $this->assertEquals('/node/' . $node->id(), $path_of_node);

    // Add a translation, verify it is being saved as expected.
    $uuid_resolver->resetStaticCache();
    $translation = $node->addTranslation('fr', $node->toArray());
    $translation->get('path')->alias = '/petitbar';
    $translation->save();

    $node = $node_storage->load($node->id());
    $node->path->alias = '/original_en';
    $node->save();

    $path_of_node = $uuid_resolver->getAliasByUuid($node->uuid(), 'fr');
    $this->assertEquals('/petitbar', $path_of_node);

    // Check original language.
    $uuid_resolver->resetStaticCache();
    $path_of_node = $uuid_resolver->getAliasByUuid($node->uuid(), 'en');
    $this->assertEquals('/original_en', $path_of_node);

    // Update a translation, verify it is being saved as expected.
    $uuid_resolver->resetStaticCache();
    $translation = $node->getTranslation('fr');
    $translation->get('path')->alias = '/petitfoo';
    $translation->save();

    $path_of_node = $uuid_resolver->getAliasByUuid($node->uuid(), 'fr');
    $this->assertEquals('/petitfoo', $path_of_node);

    $path_of_node = $uuid_resolver->getAliasByUuid($this->randomString(), 'fr');
    $this->assertEquals(NULL, $path_of_node);
  }

  /**
   * Test CanonicalUrlController response with caching mechanism.
   */
  public function testCanonicalUrlController(): void {
    /** @var \Drupal\oe_content_canonical\ContentUuidResolver $uuid_resolver */
    $uuid_resolver = \Drupal::service('oe_content_canonical.resolver');
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');

    $node = Node::create([
      'title' => 'Testing create()',
      'type' => 'page',
      'path' => ['alias' => '/foo'],
      'status' => TRUE,
      'uid' => 0,
    ]);

    $node->save();

    // Test redirect with alias.
    $url_controller = CanonicalUrlController::create($this->container);
    $response = $url_controller->index($node->uuid());
    $target_url = $response->getTargetUrl();
    $this->assertEquals('/foo', $target_url);

    // Test redirect without alias.
    $node->path->alias = '';
    $node->save();

    $uuid_resolver->resetStaticCache();
    $response = $url_controller->index($node->uuid());
    $target_url = $response->getTargetUrl();
    $this->assertEquals('/node/' . $node->id(), $target_url);

    // Test redirect with multilingual aliases.
    $uuid_resolver->resetStaticCache();
    $translation = $node->addTranslation('fr', $node->toArray());
    $translation->get('path')->alias = '/petitbar';
    $translation->save();

    $uuid_resolver->resetStaticCache();
    $node = $node_storage->load($node->id());
    $node->path->alias = '/original_en';
    $node->save();

    $path = '/fr/content/' . $translation->uuid();
    $request = Request::create($path);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'oe_content_canonical.redirect');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route($path));
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel */
    $http_kernel = \Drupal::service('http_kernel');
    $response = $http_kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, FALSE);
    $this->assertEquals('/petitbar', $response->getTargetUrl());

    $uuid_resolver->resetStaticCache();
    $path = '/content/' . $translation->uuid();
    $request = Request::create($path);
    $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'oe_content_canonical.redirect');
    $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route($path));
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel */
    $http_kernel = \Drupal::service('http_kernel');
    $response = $http_kernel->handle($request, HttpKernelInterface::MASTER_REQUEST, FALSE);
    $this->assertEquals('/original_en', $response->getTargetUrl());
  }

}
