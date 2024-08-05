<?php

declare(strict_types=1);

namespace Drupal\oe_content_redirect_link_field\PathProcessor;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\Router;
use Drupal\Core\Url;
use Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path processor to replace path with uri from oe_redirect_link field.
 *
 * Whenever entity canonical URL is being built, we replace the resulting
 * path with the one specified in the oe_redirect_link field if one exists
 * and conditions are matched.
 *
 * In determining that the path is the one we want, we divert a bit from the
 * norm:
 *
 * - We cannot rely on the $options array because not all canonical URLs are
 *   being build from the EntityBase class. Moreover, we don't know if the route
 *   is the canonical one. For this, we use the Router service to match the
 *   path.
 * - We don't use the router.no_access_checks service because we don't
 *   want the match process to perform any enhancements as that can cause
 *   some unwanted circular consequences.
 */
class PathProcessorRedirectLink implements OutboundPathProcessorInterface {

  /**
   * The redirect link retriever service.
   *
   * @var \Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface
   */
  protected $redirectLinkResolver;

  /**
   * Constructs a PathProcessorRedirectLink object.
   *
   * @param \Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface $redirect_link_resolver
   *   The redirect link resolver.
   */
  public function __construct(RedirectLinkResolverInterface $redirect_link_resolver) {
    $this->redirectLinkResolver = $redirect_link_resolver;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    try {
      $router = new Router(\Drupal::service('router.route_provider'), \Drupal::service('path.current'), \Drupal::service('url_generator'));
      $router->setContext(\Drupal::service('router.request_context'));
      $match = $router->match($path);
    }
    catch (\Exception $e) {
      return $path;
    }

    if (!isset($match['_route'])) {
      return $path;
    }

    $route_parts = explode('.', $match['_route']);
    if (count($route_parts) !== 3 || $route_parts[0] !== 'entity' || $route_parts[2] !== 'canonical') {
      return $path;
    }

    $entity_type = $route_parts[1];
    if (!isset($match[$entity_type])) {
      return $path;
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($match[$entity_type]);
    if (!$entity instanceof ContentEntityInterface) {
      return $path;
    }
    if (isset($options['language'])) {
      $entity = $entity->hasTranslation($options['language']->getId()) ? $entity->getTranslation($options['language']->getId()) : $entity;
    }

    $bubbleable_metadata = $bubbleable_metadata ?? new BubbleableMetadata();

    $redirect_path = $this->redirectLinkResolver->getPath($entity, $bubbleable_metadata);
    if (!$redirect_path) {
      return $path;
    }

    $options['prefix'] = '';

    if (UrlHelper::isExternal($redirect_path)) {
      $options['base_url'] = $redirect_path;
      // Remove the language option as we don't want to append a language code
      // to the external URL.
      if (isset($options['language'])) {
        unset($options['language']);
      }

      // We don't want existing query parameters or fragment to be appended
      // to the redirect link which may or may not contain its own.
      if (isset($options['query'])) {
        $options['query'] = [];
      }

      if (isset($options['fragment'])) {
        // Unfortunately the fragment cannot be removed because the UrlGenerator
        // doesn't honor changes made here so it will still be appended.
        // @see https://www.drupal.org/project/drupal/issues/2881077.
        unset($options['fragment']);
      }

      return '';
    }

    $parsed = UrlHelper::parse($redirect_path);

    try {
      // If the URL is internal, we don't want to regenerate it with all the
      // same options (such as query and fragment). Moreover, we want to remove
      // the base path when we generate it here so that it can get added later.
      // Unfortunately, the fragment is processed before this processor so for
      // internal URLs we cannot keep the fragment.
      $url = Url::fromUri($parsed['path'], ['base_url' => ''] + $options)->toString(TRUE);
      $bubbleable_metadata->addCacheableDependency($url);
      $options['query'] = $parsed['query'];

      return $url->getGeneratedUrl();
    }
    catch (\Exception $exception) {
      return $path;
    }
  }

}
