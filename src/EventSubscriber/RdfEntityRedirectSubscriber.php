<?php

declare(strict_types = 1);

namespace Drupal\oe_content\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rdf_entity\RdfInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirects from the RDF URI to the canonical URL of the entity.
 *
 * @todo remove this when OPENEUROPA-1194 is in.
 */
class RdfEntityRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs RdfEntityRedirectSubscriber.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[KernelEvents::REQUEST] = 'onRequest';
    return $events;
  }

  /**
   * Performs the redirect.
   *
   * In case the URI of an RDF entity is requested, redirect to the canonical
   * URL of that entity if possible.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The event.
   */
  public function onRequest(GetResponseEvent $event): void {
    $request = $event->getRequest();
    $path = $request->getPathInfo();
    $exploded = explode('/', trim($path, '/'));
    if (count($exploded) !== 3 || $exploded[0] !== 'rdf_entity') {
      return;
    }

    global $base_url;
    $base = $base_url;
    $uri = $base . $path;
    $entity = $this->entityTypeManager->getStorage('rdf_entity')->load($uri);
    if (!$entity instanceof RdfInterface) {
      return;
    }

    $url = $entity->toUrl();
    $response = new RedirectResponse($url->toString());
    $event->setResponse($response);
    if ($request->query->has('destination')) {
      $request->query->remove('destination');
    }
  }

}
