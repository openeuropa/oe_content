<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Routing\TrustedRedirectResponse;
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
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(ContentUrlResolverInterface $url_resolver, ContentUuidResolverInterface $uuid_resolver, LanguageManagerInterface $language_manager) {
    $this->contentUrlResolver = $url_resolver;
    $this->contentUuidResolver = $uuid_resolver;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('oe_content_persistent.url_resolver'),
      $container->get('oe_content_persistent.uuid_resolver'),
      $container->get('language_manager')
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
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $entity = $this->contentUuidResolver->getEntityByUuid($uuid, $langcode);
    if (!$entity instanceof ContentEntityInterface) {
      throw new NotFoundHttpException();
    }

    // We need to resolve the URL in a render context because we can never know
    // what a subscriber does for determining the URL and we may be leaking
    // cache metadata.
    $context = new RenderContext();
    /** @var \Drupal\Core\Url $url */
    $url = \Drupal::service('renderer')->executeInRenderContext($context, function () use ($entity) {
      return $this->contentUrlResolver->resolveUrl($entity);
    });

    $generated_url = $url->toString(TRUE);
    $cache = CacheableMetadata::createFromObject($generated_url);

    if (!$context->isEmpty()) {
      $bubbleable_metadata = $context->pop();
      $cache->addCacheableDependency($bubbleable_metadata);
    }

    $cache->addCacheableDependency($entity);
    $cache->addCacheContexts(['url', 'languages']);
    $response = new TrustedRedirectResponse($generated_url->getGeneratedUrl());
    $response->addCacheableDependency($cache);
    return $response;
  }

}
