<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\EventSubscriber;

use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Allows manipulation of the response object when performing a redirect.
 */
class PersistentUrlRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * Constructs a PersistentUrlRedirectSubscriber object.
   *
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The service for transforming uuid to alias/system path.
   */
  public function __construct(ContentUuidResolverInterface $uuid_resolver) {
    $this->contentUuidResolver = $uuid_resolver;
  }

  /**
   * Allows manipulation of the response object when performing a redirect.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The Event to process.
   */
  public function updateRedirectCacheability(FilterResponseEvent $event): void {
    $response = $event->getResponse();
    if ($response instanceof LocalRedirectResponse && $response->headers->get('PURL', FALSE) === TRUE) {
      $response->getCacheableMetadata()->addCacheTags($this->contentUuidResolver->getCacheTags());
    }

  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['updateRedirectCacheability', -2000];
    return $events;
  }

}
