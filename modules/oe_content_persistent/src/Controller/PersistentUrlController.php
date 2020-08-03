<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\oe_content_persistent\ContentUrlResolverInterface;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller that redirects to an entity based on its UUID.
 */
class PersistentUrlController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Content URL resolver service.
   *
   * @var \Drupal\oe_content_persistent\ContentUrlResolverInterface
   */
  protected $contentUrlResolver;

  /**
   * The content UUID resolver.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * PersistentUrlController constructor.
   *
   * @param \Drupal\oe_content_persistent\ContentUrlResolverInterface $url_resolver
   *   The content URL resolver service.
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The content UUID resolver.
   */
  public function __construct(ContentUrlResolverInterface $url_resolver, ContentUuidResolverInterface $uuid_resolver) {
    $this->contentUrlResolver = $url_resolver;
    $this->contentUuidResolver = $uuid_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oe_content_persistent.url_resolver'),
      $container->get('oe_content_persistent.uuid_resolver')
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
        $url = $this->contentUrlResolver->resolveUrl($entity);
        return new RedirectResponse($url->toString(), 302, ['PURL' => '1']);
      }
    }

    throw new NotFoundHttpException();
  }

}
