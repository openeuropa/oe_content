<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for vocabulary reference handler plugins.
 */
abstract class VocabularyReferenceHandlerPluginBase extends PluginBase implements VocabularyReferenceHandlerInterface, ContainerFactoryPluginInterface {

  /**
   * The selection plugin manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * Constructs a VocabularyReferenceHandlerPluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager
   *   The selection plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SelectionPluginManagerInterface $selection_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->selectionManager = $selection_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.entity_reference_selection')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
