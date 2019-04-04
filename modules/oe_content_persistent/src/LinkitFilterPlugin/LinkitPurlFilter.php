<?php

declare(strict_types = 1);

namespace Drupal\oe_content_persistent\LinkitFilterPlugin;

use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\linkit\SubstitutionManagerInterface;
use Drupal\oe_content_persistent\ContentUuidResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides overriden linkit filter.
 */
class LinkitPurlFilter extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The Content UUID transformer to alias/system path.
   *
   * @var \Drupal\oe_content_persistent\ContentUuidResolverInterface
   */
  protected $contentUuidResolver;

  /**
   * The substitution manager.
   *
   * @var \Drupal\linkit\SubstitutionManagerInterface
   */
  protected $substitutionManager;

  /**
   * Constructs a LinkitFilter object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\oe_content_persistent\ContentUuidResolverInterface $uuid_resolver
   *   The service for transforming uuid to alias/system path.
   * @param \Drupal\linkit\SubstitutionManagerInterface $substitution_manager
   *   The substitution manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContentUuidResolverInterface $uuid_resolver, SubstitutionManagerInterface $substitution_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->contentUuidResolver = $uuid_resolver;
    $this->substitutionManager = $substitution_manager;
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
      $container->get('plugin.manager.linkit.substitution')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $result = new FilterProcessResult($text);

    if (strpos($text, 'data-entity-type') !== FALSE && strpos($text, 'data-entity-uuid') !== FALSE) {
      $dom = Html::load($text);
      $xpath = new \DOMXPath($dom);

      foreach ($xpath->query('//a[@data-entity-type and @data-entity-uuid]') as $element) {
        /** @var \DOMElement $element */
        try {
          // Load the appropriate translation of the linked entity.
          $uuid = $element->getAttribute('data-entity-uuid');

          $entity = $this->contentUuidResolver->getEntityByUuid($uuid, $langcode);
          if ($entity) {

            /** @var \Drupal\Core\GeneratedUrl $url */
            $url = $this->substitutionManager
              ->createInstance($substitution_type)
              ->getUrl($entity);

            $element->setAttribute('href', $url->getGeneratedUrl());
            $access = $entity->access('view', NULL, TRUE);

            // Set the appropriate title attribute.
            if ($this->settings['title'] && !$access->isForbidden() && !$element->getAttribute('title')) {
              $element->setAttribute('title', $entity->label());
            }

            // The processed text now depends on:
            $result
              // - the linked entity access for the current user.
              ->addCacheableDependency($access)
              // - the generated URL (which has undergone path & route
              // processing)
              ->addCacheableDependency($url)
              // - the linked entity (whose URL and title may change)
              ->addCacheableDependency($entity);
          }
        }
        catch (\Exception $e) {
          watchdog_exception('linkit_filter', $e);
        }
      }

      $result->setProcessedText(Html::serialize($dom));
    }

    return $result;
  }

}
