<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Drupal\oe_content_persistent\Event\PersistentUrlResolverEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a filter to convert PURL into internal urls/aliases.
 *
 * @Filter(
 *   id = "filter_purl",
 *   title = @Translation("Convert Persistent Uniform Resource Locator into
 *   URLs"), type =
 *   Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FilterPurl extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The Content UUID resolver service.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * The config of PURL.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $purlConfig;

  /**
   * The config of the site.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $siteConfig;

  /**
   * Constructs a new FilterPurl object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The content UUID resolver service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, ContentUuidResolverInterface $uuid_resolver, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventDispatcher = $event_dispatcher;
    $this->contentUuidResolver = $uuid_resolver;
    $this->purlConfig = $config_factory->get('oe_content_persistent.settings');
    $this->siteConfig = $config_factory->get('system.site');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('oe_content_persistent.resolver'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    $url_regexp = '/^' . preg_quote($this->purlConfig->get('base_url'), '/') . '(' . Uuid::VALID_PATTERN . ')/i';

    $result->addCacheableDependency($this->purlConfig);

    /** @var \DOMElement $node */
    foreach ($xpath->query('//a') as $node) {
      try {
        $href = $node->getAttribute('href');
        // If there is a 'href' attribute that contains a UUID,
        // set it to the entity's current URL. This ensures that the URL works
        // even after a change of entity storage or import of data.
        if (!preg_match($url_regexp, $href, $matches)) {
          continue;
        }

        $uuid = $matches[1];
        if (!Uuid::isValid($uuid)) {
          continue;
        }

        // We try to load the entity based on the UUID. If we fail, however,
        // we link to the 404 page of the site so that it mirrors the default
        // effect of the referenced entity being deleted from the system.
        $entity = $this->contentUuidResolver->getEntityByUuid($uuid, $langcode);
        if ($entity instanceof EntityInterface) {
          // Not all entity types will need to be linked to their
          // canonical URLs so we dispatch an event to allow to modify
          // the resulting URL.
          $event = new PersistentUrlResolverEvent($entity);
          $this->eventDispatcher->dispatch(PersistentUrlResolverEvent::NAME, $event);
          $url = is_null($event->getUrl()) ? $entity->toUrl() : $event->getUrl();
          $parsed_href = UrlHelper::parse($href);
          $url = $url->setOption('query', $parsed_href['query'])
            ->setOption('fragment', $parsed_href['fragment'])
            ->toString(TRUE);
          $result->addCacheableDependency($entity);
        }
        else {
          $url = $this->getDefaultPageNotFoundUrl()->toString(TRUE);
          $result->addCacheableDependency($this->siteConfig);
        }
        $node->setAttribute('href', $url->getGeneratedUrl());

        $result->addCacheableDependency($url);
      }
      catch (\Exception $e) {
        watchdog_exception('filter_purl', $e);
      }
    }

    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

  /**
   * Returns the default URL for a 404 page.
   *
   * @return \Drupal\Core\Url
   *   The URL.
   */
  protected function getDefaultPageNotFoundUrl(): Url {
    $path = $this->siteConfig->get('page.404');
    if (!empty($path)) {
      return Url::fromUserInput($path);
    }

    return Url::fromRoute('system.404');
  }

}
