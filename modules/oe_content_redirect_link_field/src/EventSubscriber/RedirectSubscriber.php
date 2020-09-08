<?php

declare(strict_types = 1);

namespace Drupal\oe_content_redirect_link_field\EventSubscriber;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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
   * Constructs event subscriber.
   *
   * @param \Drupal\oe_content_redirect_link_field\RedirectLinkResolverInterface $redirect_link_resolver
   *   The redirect link resolver.
   */
  public function __construct(RedirectLinkResolverInterface $redirect_link_resolver) {
    $this->redirectLinkResolver = $redirect_link_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => ['onKernelRequest', 31],
    ];
  }

  /**
   * Handle request event for applying redirect if needed.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   Response event.
   */
  public function onKernelRequest(GetResponseEvent $event): void {
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
    if (empty($request->attributes->get($entity_type))) {
      return;
    }

    if (!$request->attributes->get($entity_type) instanceof ContentEntityInterface) {
      return;
    }

    $entity = $request->attributes->get($entity_type);

    $bubbleable_metadata = new BubbleableMetadata();
    $bubbleable_metadata->addCacheContexts(['languages:language_interface']);
    $redirect_path = $this->redirectLinkResolver->getPath($entity, $bubbleable_metadata);

    if (!$redirect_path) {
      return;
    }

    if (!UrlHelper::isExternal($redirect_path)) {
      $redirect_path = $request->getUriForPath($redirect_path);
    }

    $redirect_response = new TrustedRedirectResponse($redirect_path, 301);
    $redirect_response->addCacheableDependency($bubbleable_metadata);
    $event->setResponse($redirect_response);
    // For avoiding possible double handling or override of request in next
    // request event subscribers (like in 'redirect' module), we have to stop
    // further propagation of the event, if we have a URL from the value of
    // the redirect link field.
    $event->stopPropagation();
  }

}
