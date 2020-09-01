<?php

declare(strict_types = 1);

namespace Drupal\oe_content_redirect_link_field;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\Routing\Route;

/**
 * Default implementation of retrieving redirect link from path.
 */
class RetrieveRedirectLink implements RetrieveRedirectLinkInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a RetrieveRedirectLink object.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current active user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getPath(string $path, array &$options, BubbleableMetadata $bubbleable_metadata = NULL): string {
    $bubbleable_metadata = $bubbleable_metadata ?? new BubbleableMetadata();

    if (empty($options['route'])) {
      return $path;
    }

    $bubbleable_metadata->addCacheContexts(['user.permissions']);

    /** @var \Symfony\Component\Routing\Route $route */
    $route = $options['route'];

    // Stop path processing if route is not canonical.
    if (!$this->isCanonical($route)) {
      return $path;
    }

    if (!$node = $this->getEntity($path)) {
      return $path;
    }
    $bubbleable_metadata->addCacheableDependency($node);

    // Stop path processing if target node do not have or have empty redirect
    // link field.
    if (!$redirect_link = $this->getRedirectLink($node, $options)) {
      return $path;
    }

    if (!empty($options['language'])) {
      $bubbleable_metadata->addCacheContexts(['languages:language_interface']);
    }

    // Stop path processing if user have
    // 'bypass redirect link outbound rewriting' permission.
    if ($this->currentUser->hasPermission('bypass redirect link outbound rewriting')) {
      return $path;
    }

    if (UrlHelper::isExternal($redirect_link)) {
      unset($options['prefix']);
      unset($options['route']);
      unset($options['language']);
      $options['external'] = TRUE;
      $options['base_url'] = $redirect_link;
      return '';
    }
    else {
      $options['external'] = FALSE;
      $options['prefix'] = '';
      return Url::fromUri($redirect_link, [
        'base_url' => '',
        'language' => $options['language'],
        'prefix' => '',
      ])->toString();
    }
  }

  /**
   * Determine if route is canonical.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route.
   *
   * @return bool
   *   Is canonical path.
   */
  protected function isCanonical(Route $route): bool {
    return $route->getPath() === '/node/{node}';
  }

  /**
   * Get node instance of target path.
   *
   * @param string $path
   *   The path string.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node object.
   */
  protected function getEntity(string $path): ?NodeInterface {
    // Unfortunately we cannot use 'router.no_access_checks' service through
    // dependency injection for getting full route object due to
    // circular reference to 'router.route_provider' and related to this
    // exception. Using service through \Drupal::service() doesn't look
    // preferable.
    if (preg_match('!^/node/([0-9]+)(/.*)?!', $path, $matches) === FALSE) {
      return NULL;
    }
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->entityTypeManager->getStorage('node')->load($matches[1]);
    return $node;
  }

  /**
   * Get redirect link if possible.
   *
   * @param \Drupal\node\NodeInterface|null $node
   *   The Node object.
   * @param array $options
   *   The array of URL options.
   */
  protected function getRedirectLink(?NodeInterface $node, array $options): ?string {
    if (!$node->hasField('oe_redirect_link') || $node->get('oe_redirect_link')->isEmpty()) {
      return NULL;
    }

    if (!empty($options['language']) && $node->hasTranslation($options['language']->getId())) {
      $node_translated = $node->getTranslation($options['language']->getId());
    }

    if (empty($node_translated) || $node_translated->isDefaultTranslation() || $node_translated->get('oe_redirect_link')->isEmpty()) {
      return $node->get('oe_redirect_link')->getString();
    }
    else {
      return $node_translated->get('oe_redirect_link')->getString();
    }
  }

}
