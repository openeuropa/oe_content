<?php

declare(strict_types=1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\EntityInterface;

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

}
