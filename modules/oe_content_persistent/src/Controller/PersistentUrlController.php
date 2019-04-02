<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns response for redirect to node (or other entity types) aliases.
 */
class PersistentUrlController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * PersistentUrlController constructor.
   *
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The service for transforming uuid to alias/system path.
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
   * The controller callback method for handling redirect of persistent urls.
   *
   * @param string $uuid
   *   The UUID of content for redirect.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to actual alias or system path.
   */
  public function index(string $uuid): RedirectResponse {
    if ($alias = $this->contentUuidResolver->getAliasByUuid($uuid)) {
      // Unfortunately we can't use instance of CacheableSecuredRedirectResponse
      // because we have exception inside
      // \Drupal\Core\EventSubscriber\EarlyRenderingControllerWrapperSubscriber
      // class.
      // More infor you could find in this article:
      // https://www.lullabot.com/articles/early-rendering-a-lesson-in-debugging-drupal-8
      return new RedirectResponse($alias, 302, ['PURL' => TRUE]);
    }

    return $this->redirect('system.404');
  }

}
