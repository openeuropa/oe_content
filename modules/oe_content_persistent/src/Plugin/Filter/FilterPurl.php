<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
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
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The content UUID resolver service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContentUuidResolverInterface $uuid_resolver, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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

    foreach ($xpath->query('//a') as $node) {
      try {
        $href = $node->getAttribute('href');
        // If there is a 'href' attribute that contains a UUID,
        // set it to the entity's current URL. This ensures that the URL works
        // even after a change of entity storage or import of data.
        if (preg_match($url_regexp, $href, $matches) && Uuid::isValid($matches[1]) && $uuid = $matches[1]) {
          $entity = $this->contentUuidResolver->getEntityByUuid($uuid, $langcode);
          if ($entity) {
            $url = $entity->toUrl()->toString(TRUE);
            $node->setAttribute('href', $url->getGeneratedUrl());
            $result
              ->addCacheableDependency($entity)
              ->addCacheableDependency($url);
          }
          // If we didn't find any entity with the provided UUID,
          // set the address to the site's 404 page.
          else {
            $custom_404_path = $this->siteConfig->get('page.404');
            if (!empty($custom_404_path)) {
              $url = Url::fromUserInput($custom_404_path)->toString(TRUE)->getGeneratedUrl();
            }
            else {
              $url = Url::fromRoute('system.404')->toString(TRUE)->getGeneratedUrl();
            }
            $node->setAttribute('href', $url);
            $result
              ->addCacheableDependency($this->siteConfig)
              ->addCacheableDependency($url);
          }
        }
      }
      catch (\Exception $e) {
        watchdog_exception('filter_purl', $e);
      }
    }
    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

}
