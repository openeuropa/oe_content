<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Url;

/**
 * Default implementation of Content UUID resolver.
 */
class ContentUuidResolver implements ContentUuidResolverInterface {

  use RefinableCacheableDependencyTrait;

  /**
   * Holds the map of uuid lookups per language.
   *
   * @var array
   */
  protected $lookupMap = [];

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
   * List of supported storages.
   *
   * @var array
   */
  protected $supportedStorages;

  /**
   * Constructs a ContentUuidResolver.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Path\AliasManagerInterface $alias_manager
   *   The alias manager.
   * @param array $supported_storages
   *   List of supported storages.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, AliasManagerInterface $alias_manager, array $supported_storages = []) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
    $this->supportedStorages = $supported_storages;
  }

  /**
   * Clear static cache.
   */
  public function resetStaticCache(): void {
    $this->lookupMap = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasByUuid(string $uuid, string $langcode = NULL): ?string {
    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();

    // Here we are trying to use static cache.
    if (!empty($this->lookupMap[$uuid][$langcode])) {
      return $this->lookupMap[$uuid][$langcode];
    }

    // Try to retrieve entities from supported storages.
    foreach ($this->supportedStorages as $storage_type) {
      $storage = $this->entityTypeManager->getStorage($storage_type);
      // Retrieving correct entity by uuid as we can't get entity internal url
      // without loading full entity.
      $entities = $storage->loadByProperties(['uuid' => $uuid]);
      if (empty($entities)) {
        return NULL;
      }

      $entity = reset($entities);
      if ($entity instanceof EntityInterface) {
        $alias = $this->aliasManager->getAliasByPath('/' . $entity->toUrl()->getInternalPath(), $langcode);
        // Normalize url with related parts like langcode prefix and base url.
        $this->lookupMap[$uuid][$langcode] = Url::fromUserInput($alias)->toString();
        // Used for bubble up cache tags to page cache level.
        $this->addCacheableDependency($entity);
        break;
      }
    }

    return $this->lookupMap[$uuid][$langcode] ?? NULL;
  }

}
