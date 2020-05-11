<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

class VocabularyReferenceWidgetPluginManager extends DefaultPluginManager implements VocabularyReferenceWidgetPluginManagerInterface {

  /**
   * Constructs a VocabularyReferenceWidgetPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/VocabularyReferenceWidget',
      $namespaces,
      $module_handler,
      'Drupal\oe_taxonomy_types\VocabularyReferenceWidgetInterface',
      'Drupal\oe_taxonomy_types\Annotation\VocabularyReferenceWidget'
    );
    $this->alterInfo('vocabulary_reference_widget_info');
    $this->setCacheBackend($cache_backend, 'vocabulary_reference_widget_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitionsAsOptions(): array {
    $options = [];

    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }

    return $options;
  }

}
