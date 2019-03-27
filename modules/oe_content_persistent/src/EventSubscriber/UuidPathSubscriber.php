<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\EventSubscriber;

use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides a path subscriber that converts path aliases.
 */
class UuidPathSubscriber implements EventSubscriberInterface {

  /**
   * Provides methods for converter UUID to entity persistent url or alias.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $uuidResolver;

  /**
   * Constructs a new ContentUuidSubscriber instance.
   *
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   Provides methods for converter UUID to entity canonical url or alias.
   */
  public function __construct(ContentUuidResolverInterface $uuid_resolver) {
    $this->uuidResolver = $uuid_resolver;
  }

  /**
   * Ensures system paths for the request get cached.
   */
  public function onKernelTerminate(PostResponseEvent $event): void {
    $this->uuidResolver->writeCache();
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::TERMINATE][] = ['onKernelTerminate', 200];
    return $events;
  }

}
