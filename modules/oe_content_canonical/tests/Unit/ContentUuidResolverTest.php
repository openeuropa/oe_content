<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_canonical\Unit;

use Drupal\Component\Uuid\Php;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\oe_content_canonical\ContentUuidResolver;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\oe_content_canonical\ContentUuidResolver
 * @group OE Content canonical
 */
class ContentUuidResolverTest extends UnitTestCase {

  /**
   * The mock node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $nodeStorage;

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_canonical\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $languageManager;

  /**
   * The alias manager that caches alias lookups based on the request.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $aliasManager;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $cache;

  /**
   * List of allowed entity types.
   *
   * @var array
   */
  protected $entityTypes = ['node'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->createMock('Drupal\Core\Entity\EntityTypeManagerInterface');
    $this->languageManager = $this->createMock('Drupal\Core\Language\LanguageManagerInterface');
    $this->aliasManager = $this->createMock('Drupal\Core\Path\AliasManagerInterface');
    $this->cache = $this->createMock('Drupal\Core\Cache\CacheBackendInterface');

    $this->nodeStorage = $this->createMock('Drupal\Core\Entity\EntityStorageInterface');

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->with('node')
      ->will($this->returnValue($this->nodeStorage));

    $this->contentUuidResolver = new ContentUuidResolver($this->entityTypeManager, $this->languageManager, $this->aliasManager, $this->cache, $this->entityTypes);

  }

  /**
   * Test getting correct alias if we don't have correct alias.
   */
  public function testGetAliasByUuidMatch() {
    $uuid = new Php();
    $random_uuid = $uuid->generate();

    $language = new Language(['id' => 'en']);

    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->with(LanguageInterface::TYPE_URL)
      ->will($this->returnValue($language));

    $test_node = $this->getMockBuilder('Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();

    $uri = $this->prophesize(Url::class);
    $uri->toString()->willReturn('/node/777');

    $test_node->expects($this->any())
      ->method('toUrl')
      ->will($this->returnValue($uri->reveal()));

    $this->nodeStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['uuid' => $random_uuid])
      ->will($this->returnValue([$test_node]));

    $this->aliasManager->expects($this->any())
      ->method('getAliasByPath')
      ->with('/node/777')
      ->will($this->returnValue('/node/777'));

    $this->assertEquals('/node/777', $this->contentUuidResolver->getAliasByUuid($random_uuid));
  }

  /**
   * Test getting NULL if we don't have correct alias.
   */
  public function testGetAliasByUuidNotMatch() {
    $uuid = new Php();
    $random_uuid = $uuid->generate();

    $language = new Language(['id' => 'en']);

    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->with(LanguageInterface::TYPE_URL)
      ->will($this->returnValue($language));

    $this->contentUuidResolver->getAliasByUuid($random_uuid);

    $this->assertEquals(NULL, $this->contentUuidResolver->getAliasByUuid($random_uuid));

  }

  /**
   * Test getting alias by uuid with cache usage.
   */
  public function testGetAliasByUuidWithCache() {
    $uuid = new Php();
    $random_uuid = $uuid->generate();

    $language = new Language(['id' => 'en']);

    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->with(LanguageInterface::TYPE_URL)
      ->will($this->returnValue($language));

    $this->contentUuidResolver->setCacheKey($random_uuid);

    $test_node = $this->getMockBuilder('Drupal\node\Entity\Node')
      ->disableOriginalConstructor()
      ->getMock();

    $uri = $this->prophesize(Url::class);
    $uri->toString()->willReturn('/node/777');

    $test_node->expects($this->any())
      ->method('toUrl')
      ->will($this->returnValue($uri->reveal()));

    $test_node->expects($this->any())
      ->method('getCacheTags')
      ->will($this->returnValue([$random_uuid => ['node:777']]));

    $this->nodeStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['uuid' => $random_uuid])
      ->will($this->returnValue([$test_node]));

    $this->aliasManager->expects($this->any())
      ->method('getAliasByPath')
      ->with('/node/777')
      ->will($this->returnValue('/node/777'));

    $this->contentUuidResolver->getAliasByUuid($random_uuid);

    $this->cache->expects($this->once())
      ->method('set');

    $this->contentUuidResolver->writeCache();

    // Now we are using static cache.
    $this->nodeStorage->expects($this->never())
      ->method('loadByProperties');

    $this->contentUuidResolver->getAliasByUuid($random_uuid);

    // Now we are testing usage of cached path match.
    $this->contentUuidResolver->resetStaticCache();

    $this->nodeStorage->expects($this->never())
      ->method('loadByProperties');

    $this->cache->expects($this->once())
      ->method('get')
      ->with('content_uuid:en:' . $random_uuid)
      ->will($this->returnValue((object) ['data' => '/node/777']));

    $this->contentUuidResolver->getAliasByUuid($random_uuid);
  }

}
