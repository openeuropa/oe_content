<?php

declare(strict_types = 1);

namespace Drupal\oe_content_canonical;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\CacheDecorator\CacheDecoratorInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;

/**
 * Default implementation of Content UUID resolver.
 */
class ContentUuidResolver implements ContentUuidResolverInterface, CacheDecoratorInterface {

  /**
   * Holds the map of uuid lookups per language.
   *
   * @var array
   */
  protected $lookupMap = [];

  /**
   * The cache key to use when caching paths.
   *
   * @var string
   */
  protected $cacheKey;

  /**
   * The cache tags to use when caching paths.
   *
   * @var array
   */
  protected $cacheTags = [];

  /**
   * Whether the cache needs to be written.
   *
   * @var bool
   */
  protected $cacheNeedsWriting = FALSE;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The alias manager that caches alias lookups based on the request.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * List of allowed entity types.
   *
   * @var array
   */
  protected $entityTypes;

  /**
   * Constructs an ContentUuidResolver.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The alias manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param array $entity_types
   *   List of allowed entity types.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, AliasManagerInterface $alias_manager, CacheBackendInterface $cache, array $entity_types = []) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->cache = $cache;
    $this->entityTypes = $entity_types;
  }

  /**
   * {@inheritdoc}
   */
  public function setCacheKey($key) {
    $this->cacheKey = $this->getCachePrefixKey() . $key;
  }

  /**
   * Getting current cache key.
   */
  public function getCacheKey() {
    return $this->cacheKey;
  }

  /**
   * Clear static cache.
   */
  public function resetStaticCache(): void {
    $this->cacheTags = [];
    $this->lookupMap = [];
    $this->cacheNeedsWriting = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function writeCache() {
    // We assume that better to write cache only on request finishing
    // on Controller page.
    if ($this->cacheNeedsWriting === TRUE && !empty($this->cacheKey) && !empty($this->lookupMap)) {
      $twenty_four_hours = 60 * 60 * 24;

      if ($cache_tags = reset($this->cacheTags)) {
        $this->cache->set($this->cacheKey, reset($this->lookupMap), $this->getRequestTime() + $twenty_four_hours, $cache_tags);
      }
      else {
        $this->cache->set($this->cacheKey, reset($this->lookupMap), $this->getRequestTime() + $twenty_four_hours);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasByUuid(string $uuid, string $langcode = NULL): ?string {
    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();

    // Here we are trying to use static cache.
    if (!empty($this->lookupMap[$uuid])) {
      return $this->lookupMap[$uuid];
    }

    // Using cache key initialized in controller
    // (if we use service inside controller).
    if ($this->cacheKey) {
      if ($cached = $this->cache->get($this->cacheKey)) {
        return $cached->data;
      }
      else {
        // Informing about cache writing on kernel termination.
        // @see \Drupal\oe_content_canonical\EventSubscriber\UuidPathSubscriber and $this->writeCache().
        $this->cacheNeedsWriting = TRUE;
      }
    }
    // Reuse previously cached alias of uuid,
    // even if we don't use this service with controller.
    elseif ($cached = $this->cache->get($this->getCachePrefixKey() . $uuid)) {
      return $cached->data;
    }

    // Try to retrieve alias or system path from allowed entity types.
    foreach ($this->entityTypes as $entity_type) {
      $storage = $this->entityTypeManager->getStorage($entity_type);
      // Retrieving correct entity by uuid as we can't get entity internal url
      // without loading full entity.
      $entities = $storage->loadByProperties(['uuid' => $uuid]);
      if (empty($entities)) {
        return NULL;
      }

      $entity = reset($entities);
      if ($entity instanceof EntityInterface) {
        $alias = $this->aliasManager->getAliasByPath('/' . $entity->toUrl()->getInternalPath(), $langcode);
        $this->lookupMap[$uuid] = Url::fromUserInput($alias)->toString();
        $this->cacheTags[$uuid] = $entity->getCacheTags();
        break;
      }
    }

    return $this->lookupMap[$uuid] ?? NULL;
  }

  /**
   * Return prefix for cache key.
   */
  protected function getCachePrefixKey(): string {
    $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    return 'content_uuid:' . $langcode . ':';
  }

  /**
   * Wrapper method for REQUEST_TIME constant.
   *
   * @return int
   *   Return current timestamp.
   */
  protected function getRequestTime(): int {
    return defined('REQUEST_TIME') ? REQUEST_TIME : (int) $_SERVER['REQUEST_TIME'];
  }

}
