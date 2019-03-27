<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

/**
 * Provides methods for converter UUID to entity persistent url or alias.
 */
interface ContentUuidResolverInterface {

  /**
   * Get Alias from content UUID.
   *
   * @param string $uuid
   *   UUID of a content.
   * @param string $langcode
   *   Language of a content.
   *
   * @return string|null
   *   Url for target content.
   */
  public function getAliasByUuid(string $uuid, string $langcode = NULL): ?string;

}
