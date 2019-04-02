<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to convert PURL into internal urls/aliases.
 *
 * @Filter(
 *   id = "filter_purl",
 *   title = @Translation("Convert Persistent Uniform Resource Locator into URLs"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 */
class FilterPurl extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * Constructs a \Drupal\editor\Plugin\Filter\EditorFileReference object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The service for transforming uuid to alias/system path.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContentUuidResolverInterface $uuid_resolver) {
    $this->contentUuidResolver = $uuid_resolver;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('oe_content_persistent.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    foreach ($xpath->query('//a') as $node) {
      $href = $node->getAttribute('href');

      preg_match('/\/content\/([0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12})/i', $href, $matches);

      // If there is a 'src' attribute, set it to the file entity's current
      // URL. This ensures the URL works even after the file location changes.
      if (preg_match('/[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}/i', $matches[1]) && $uuid = $matches[1]) {
        $alias = $this->contentUuidResolver->getAliasByUuid($matches[1], $langcode);
        if ($alias) {
          $node->setAttribute('href', $alias);
          $result->addCacheTags($this->contentUuidResolver->getCacheTags());
        }
      }
    }
    $result->setProcessedText(Html::serialize($dom));

    return $result;
  }

}
