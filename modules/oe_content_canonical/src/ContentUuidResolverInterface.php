<?php

declare(strict_types=1);

use Drupal\Core\Entity\EntityMalformedException;


/**
 * Provides an interface for Content UUID converter from UUID to entity
 * canonical url or alias.
 */
interface ContentUuidResolverInterface {

  /**
   * Get Alias from content UUID.
   *
   * @param string $uuid
   *   UUID of a content.
   *
   * @return string
   *   Url for target content.
   *
   * @throws EntityMalformedException
   */
  public function getAliasByUuid(string $uuid): string;
}
