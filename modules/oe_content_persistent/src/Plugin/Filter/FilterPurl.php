<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\oe_content_persistent\ContentUrlResolverInterface;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * The Content URL resolver service.
   *
   * @var \Drupal\oe_content_persistent\ContentUrlResolverInterface
   */
  protected $contentUrlResolver;

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
   * @param \Drupal\oe_content_persistent\ContentUrlResolverInterface $url_resolver
   *   The content URL resolver service.
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The content UUID resolver service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContentUrlResolverInterface $url_resolver, ContentUuidResolverInterface $uuid_resolver, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->contentUrlResolver = $url_resolver;
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
      $container->get('oe_content_persistent.url_resolver'),
      $container->get('oe_content_persistent.uuid_resolver'),
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
        if ($entity instanceof ContentEntityInterface) {
          // Not all entity types will need to be linked to their
          // canonical URLs so use the url resolver to get the final URL.
          $url = $this->contentUrlResolver->resolveUrl($entity);
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
