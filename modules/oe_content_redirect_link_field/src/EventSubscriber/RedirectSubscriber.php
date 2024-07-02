<?php

declare(strict_types=1);

namespace Drupal\oe_content_redirect_link_field\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * OpenEuropa Redirect Link Field event subscriber.
 */
class RedirectSubscriber implements EventSubscriberInterface {

  /**
   * The redirect link retriever service.
   *
   * @var \Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface
   */
  protected $redirectLinkResolver;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface $redirectLinkResolver
   *   The redirect link resolver.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   */
  public function __construct(RedirectLinkResolverInterface $redirectLinkResolver, LanguageManagerInterface $languageManager) {
    $this->redirectLinkResolver = $redirectLinkResolver;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Subscribing after the route subscriber so that we have access to the
      // route matching.
      KernelEvents::REQUEST => ['onKernelRequest', 31],
    ];
  }

  /**
   * Redirects entity canonical URLs to their corresponding redirect links.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   Response event.
   */
  public function onKernelRequest(RequestEvent $event): void {
    $request = $event->getRequest();

    $route = $request->attributes->get('_route');
    if (empty($route)) {
      return;
    }

    $route_parts = explode('.', $route);
    if (count($route_parts) !== 3 || $route_parts[0] !== 'entity' || $route_parts[2] !== 'canonical') {
      return;
    }

    $entity_type = $route_parts[1];
    $entity = $request->attributes->get($entity_type);
    if (!$entity instanceof ContentEntityInterface) {
      return;
    }

    $current_language = $this->languageManager->getCurrentLanguage()->getId();
    $entity = $entity->hasTranslation($current_language) ? $entity->getTranslation($current_language) : $entity;

    $cache = new CacheableMetadata();
    $cache->addCacheContexts(['languages:language_interface']);
    $redirect_path = $this->redirectLinkResolver->getPath($entity, $cache);

    if (!$redirect_path) {
      return;
    }

    $redirect_path = $this->preparePath($redirect_path);
    if (!$redirect_path) {
      return;
    }

    $cache->addCacheableDependency($redirect_path);

    $redirect_response = new TrustedRedirectResponse($redirect_path->getGeneratedUrl(), 301);
    $redirect_response->addCacheableDependency($cache);
    $event->setResponse($redirect_response);
    $event->stopPropagation();
  }

  /**
   * Given the path the resolver provided, prepare it for the response.
   *
   * @param string $path
   *   The redirect path.
   *
   * @return string
   *   The redirect response URL.
   */
  protected function preparePath(string $path): ?GeneratedUrl {
    $parsed = UrlHelper::parse($path);

    try {
      return Url::fromUri($parsed['path'], [
        'fragment' => $parsed['fragment'],
        'query' => $parsed['query'],
      ])->setAbsolute()->toString(TRUE);
    }
    catch (\Exception $exception) {
      return NULL;
    }
  }

}
