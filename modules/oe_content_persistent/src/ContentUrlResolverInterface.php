<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Url;

/**
 * Interface for services that resolve the URL of an entity.
 */
interface ContentUrlResolverInterface {

  /**
   * Resolve the URL of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   A content entity.
   *
   * @return \Drupal\Core\Url
   *   The resolved URL.
   */
  public function resolveUrl(ContentEntityInterface $entity): Url;

}
