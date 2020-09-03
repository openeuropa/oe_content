<?php

declare(strict_types = 1);

namespace Drupal\oe_content_redirect_link_field;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Default implementation that resolves the content entity redirect link.
 */
class RedirectLinkResolver implements RedirectLinkResolverInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a RetrieveRedirectLink object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current active user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath(ContentEntityInterface $entity, CacheableMetadata $cacheable_metadata): ?string {
    $cacheable_metadata->addCacheContexts(['user.permissions']);
    $cacheable_metadata->addCacheableDependency($entity);

    if ($this->currentUser->hasPermission('bypass redirect link outbound rewriting')) {
      // Users with this permission do not get the redirect link.
      return NULL;
    }

    return $this->getRedirectLink($entity);
  }

  /**
   * Get the redirect link if possible.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity object.
   *
   * @return string|null
   *   The redirect link or NULL if it doesn't apply.
   */
  protected function getRedirectLink(ContentEntityInterface $entity): ?string {
    if (!$entity->hasField('oe_redirect_link')) {
      return NULL;
    }

    if ($entity->isDefaultTranslation()) {
      // If the entity is in the original language, it's enough to just retrieve
      // the redirect link.
      return !$entity->get('oe_redirect_link')->isEmpty() ? $entity->get('oe_redirect_link')->uri : NULL;
    }

    $source = $entity->getUntranslated();
    if (!$entity->isDefaultTranslation() && $entity->get('oe_redirect_link')->isEmpty()) {
      // If we are dealing with a translation which doesn't have a redirect link
      // we need to check the source if it has one and return that one.
      return !$source->get('oe_redirect_link')->isEmpty() ? $source->get('oe_redirect_link')->uri : NULL;
    }

    if (!$entity->isDefaultTranslation()) {
      // If the entity translation does have a redirect link value, we need to
      // check the source to see if it has one because we use that as a way to
      // control whether the translation should be used.
      return !$source->get('oe_redirect_link')->isEmpty() ? $entity->get('oe_redirect_link')->uri : NULL;
    }
  }

}
