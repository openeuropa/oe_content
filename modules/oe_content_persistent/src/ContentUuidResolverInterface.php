<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\TranslatableInterface;

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
   * @return \Drupal\Core\Entity\TranslatableInterface|null
   *   Url for target content.
   */
  public function getEntityByUuid(string $uuid, string $langcode = NULL): ?TranslatableInterface;

}
