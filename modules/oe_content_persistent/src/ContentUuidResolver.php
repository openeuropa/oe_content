<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Default implementation of a Content UUID resolver.
 */
class ContentUuidResolver implements ContentUuidResolverInterface {

  /**
   * Static cache of UUID lookups, per language.
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
   * List of supported entity types.
   *
   * @var array
   */
  protected $supportedEntityTypes;

  /**
   * Constructs a ContentUuidResolver.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param array $supported_entity_types
   *   List of supported entity types.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, array $supported_entity_types = []) {
    $this->entityTypeManager = $entity_type_manager;
    $this->supportedEntityTypes = $supported_entity_types;
  }

  /**
   * Resets the static cache.
   */
  public function resetStaticCache(): void {
    $this->lookupMap = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityByUuid(string $uuid, string $langcode = NULL): ?EntityInterface {
    $langcode = $langcode ?? LanguageInterface::LANGCODE_DEFAULT;

    // Try the static cache first.
    if (isset($this->lookupMap[$uuid]) && array_key_exists($langcode, $this->lookupMap[$uuid])) {
      return $this->lookupMap[$uuid][$langcode];
    }

    // Loop through the available storages and load the entity from the first
    // storage we find it in.
    foreach ($this->supportedEntityTypes as $entity_type) {
      $storage = $this->entityTypeManager->getStorage($entity_type);
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
