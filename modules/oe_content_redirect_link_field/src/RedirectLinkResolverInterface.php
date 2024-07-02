<?php

declare(strict_types=1);

namespace Drupal\oe_content_redirect_link_field;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Resolvers for the redirect link of a given entity.
 */
interface RedirectLinkResolverInterface {

  /**
   * Resolves the redirect link of a given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to resolve the redirect link from.
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheable_metadata
   *   Cacheable metadata that results in the resolve.
   *
   * @return string|null
   *   The processed path of no path was resolved.
   */
  public function getPath(ContentEntityInterface $entity, CacheableMetadata $cacheable_metadata): ?string;

  /**
   * Get the redirect link if possible.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   *
   * @return string|null
   *   The redirect link or NULL if it doesn't apply.
   */
  public function getRedirectLink(ContentEntityInterface $entity): ?string;

}
