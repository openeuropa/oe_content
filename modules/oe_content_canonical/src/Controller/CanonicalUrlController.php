<?php

declare(strict_types = 1);

namespace Drupal\oe_content_canonical\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\oe_content_canonical\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns response for redirect to node (or other entity types) aliases.
 */
class CanonicalUrlController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_canonical\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * CanonicalUrlController constructor.
   *
   * @param \Drupal\oe_content_canonical\ContentUuidResolverInterface $uuid_resolver
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
      $container->get('oe_content_canonical.resolver')
    );
  }

  /**
   * The controller callback method for handling redirect of canonical urls.
   *
   * @param string $uuid
   *   The UUID of content for redirect.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to actual alias or system path.
   */
  public function index(string $uuid): RedirectResponse {
    $this->contentUuidResolver->setCacheKey($uuid);
    if ($alias = $this->contentUuidResolver->getAliasByUuid($uuid)) {
      return new RedirectResponse($alias, 302);
    }

    return $this->redirect('system.404');
  }

}
