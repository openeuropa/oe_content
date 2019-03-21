<?php

declare(strict_types=1);

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\CacheDecorator\CacheDecoratorInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;

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
   * @var AliasManagerInterface
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
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param AliasManagerInterface $alias_manager
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

  }

  /**
   * {@inheritdoc}
   */
  public function writeCache() {

  }

  /**
   * {@inheritdoc}
   */
  public function getAliasByUuid(string $uuid): string {
    $langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();

    if (!empty($this->lookupMap[$langcode][$uuid])) {
      return $this->lookupMap[$langcode][$uuid];
    }

    foreach ($this->entityTypes as $entity_type) {
      $entity = $this->entityTypeManager->getStorage($entity_type)->loadByProperties(['uuid' => $uuid]);
      if ($entity instanceof EntityInterface) {
        $this->lookupMap[$langcode][$uuid] = $this->aliasManager->getAliasByPath($entity->toUrl(), $langcode);
        break;
      }
    }


  }


}
