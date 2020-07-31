<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Drupal\oe_content_persistent\Event\PersistentUrlResolverEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller that redirects to an entity based on its UUID.
 */
class PersistentUrlController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The content UUID resolver.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * PersistentUrlController constructor.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The content UUID resolver.
   */
  public function __construct(EventDispatcherInterface $event_dispatcher, ContentUuidResolverInterface $uuid_resolver) {
    $this->eventDispatcher = $event_dispatcher;
    $this->contentUuidResolver = $uuid_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('event_dispatcher'),
      $container->get('oe_content_persistent.resolver')
    );
  }

  /**
   * Performs the redirect based on the UUID.
   *
   * @param string $uuid
   *   The UUID of content for redirect.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to actual alias or system path.
   */
  public function index(string $uuid): RedirectResponse {
    if ($entity = $this->contentUuidResolver->getEntityByUuid($uuid)) {
      // Unfortunately we cannot use
      // an instance of CacheableSecuredRedirectResponse because we get
      // an exception inside
      // \Drupal\Core\EventSubscriber\EarlyRenderingControllerWrapperSubscriber.
      // More information you could find in this article:
      // https://www.lullabot.com/articles/early-rendering-a-lesson-in-debugging-drupal-8
      if ($entity instanceof ContentEntityInterface) {
        // Not all entity types will need to be linked to their canonical URLs,
        // so we dispatch an event to allow to modify the resulting URL.
        $event = new PersistentUrlResolverEvent($entity);
        $this->eventDispatcher->dispatch(PersistentUrlResolverEvent::NAME, $event);
        $url = is_null($event->getUrl()) ? $entity->toUrl() : $event->getUrl();
        return new RedirectResponse($url->toString(), 302, ['PURL' => '1']);
      }
    }

    throw new NotFoundHttpException();
  }

}
