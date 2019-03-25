<?php

declare(strict_types = 1);

namespace Drupal\oe_content_canonical;

/**
 * Provides methods for converter UUID to entity canonical url or alias.
 */
interface ContentUuidResolverInterface {

  /**
   * Get Alias from content UUID.
   *
   * @param string $uuid
   *   UUID of a content.
   *
   * @return string|null
   *   Url for target content.
   *
   * @throws EntityMalformedException
   */
  public function getAliasByUuid(string $uuid): ?string;

}
