<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Interface for services that resolve entities based on their UUIDs.
 */
interface ContentUuidResolverInterface {

  /**
   * Resolve an entity by its UUID and optional langcode.
   *
   * @param string $uuid
   *   UUID of a content.
   * @param string $langcode
   *   The langcode of the language the entity should be returned in.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The resolved entity or NULL.
   */
  public function getEntityByUuid(string $uuid, string $langcode = NULL): ?EntityInterface;

  /**
   * Gets the entity types that support PURL.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   An array of entity types that support Persistent uniform resource locator.
   */
  public function getSupportedEntityTypes(): array;

  /**
   * Is allowed entity type.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   Entity type object.
   *
   * @return boolean
   *   Is this content type supported.
   */
  public function isSupportedEntityType(EntityTypeInterface $entity_type): bool;

}
