<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Default implementation of Content UUID resolver.
 */
class ContentUuidResolver implements ContentUuidResolverInterface {

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
   * @param array $supported_storages
   *   List of supported storages.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, array $supported_storages = []) {
    $this->entityTypeManager = $entity_type_manager;
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
  public function getEntityByUuid(string $uuid, string $langcode = NULL): ?TranslatableInterface {
    $langcode = $langcode ?? LanguageInterface::LANGCODE_DEFAULT;

    // Try the static cache first.
    if (isset($this->lookupMap[$uuid]) && array_key_exists($langcode, $this->lookupMap[$uuid])) {
      return $this->lookupMap[$uuid][$langcode];
    }

    // Loop through the available storages and load the entity from the first
    // storage we find it in.
    foreach ($this->supportedStorages as $storage_type) {
      $storage = $this->entityTypeManager->getStorage($storage_type);
      $entities = $storage->loadByProperties(['uuid' => $uuid]);
      if (empty($entities)) {
        continue;
      }

      /** @var \Drupal\Core\Entity\TranslatableInterface $entity */
      $entity = reset($entities);
      if ($langcode !== LanguageInterface::LANGCODE_DEFAULT && $entity->hasTranslation($langcode)) {
        $this->lookupMap[$uuid][$langcode] = $entity->getTranslation($langcode);
        return $this->lookupMap[$uuid][$langcode];
      }

      $this->lookupMap[$uuid][$langcode] = $entity->getUntranslated();
      return $this->lookupMap[$uuid][$langcode];
    }

    $this->lookupMap[$uuid][$langcode] = NULL;
    return $this->lookupMap[$uuid][$langcode];
  }

}
