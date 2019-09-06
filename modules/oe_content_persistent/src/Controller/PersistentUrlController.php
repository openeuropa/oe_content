<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller that redirects to an entity based on its UUID.
 */
class PersistentUrlController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The content UUID resolver.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * PersistentUrlController constructor.
   *
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The content UUID resolver.
   */
  public function __construct(ContentUuidResolverInterface $uuid_resolver) {
    $this->contentUuidResolver = $uuid_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
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
    \Drupal::service('page_cache_kill_switch')->trigger();
    if ($entity = $this->contentUuidResolver->getEntityByUuid($uuid)) {
      // Unfortunately we cannot use
      // an instance of CacheableSecuredRedirectResponse because we get
      // an exception inside
      // \Drupal\Core\EventSubscriber\EarlyRenderingControllerWrapperSubscriber.
      // More information you could find in this article:
      // https://www.lullabot.com/articles/early-rendering-a-lesson-in-debugging-drupal-8
      if ($entity instanceof TranslatableInterface) {
        return new RedirectResponse($entity->toUrl()->toString(), 302, ['PURL' => TRUE]);
      }
    }

    throw new NotFoundHttpException();
  }

}
