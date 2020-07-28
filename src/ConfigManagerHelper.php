<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\FileStorage;

/**
 * The ConfigManagerHelper class.
 *
 * It provides additional methods to work with the configuration system.
 */
class ConfigManagerHelper {

  /**
   * Configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ConfigurationManagerHelper constructor.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   Config manager.
   */
  public function __construct(ConfigManagerInterface $config_manager) {
    $this->configManager = $config_manager;
    $this->entityTypeManager = $config_manager->getEntityTypeManager();
  }

  /**
   * Creates configuration from the *.yml file.
   *
   * @param string $config_name
   *   Config name (file name).
   * @param \Drupal\Core\Config\FileStorage $file_storage
   *   File storage instance.
   */
  public function createConfig(string $config_name, FileStorage $file_storage) {
    $entity_type = $this->configManager->getEntityTypeIdByName($config_name);

    $config_values = $file_storage->read($config_name);
    if (!$this->entityTypeManager->getStorage($entity_type)->load($config_values['id'])) {
      $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type);
      $entity_type_class = $entity_type_definition->getOriginalClass();
      $entity_type_class::create($config_values)->save();
    }
  }

  /**
   * Updates existing configuration using values from *.yml files.
   *
   * @param string $config_name
   *   Config name (file name).
   * @param \Drupal\Core\Config\FileStorage $file_storage
   *   File storage instance.
   * @param array $field_names
   *   List of fields that have to be updated. All config values will be updated
   *   if variable is empty.
   */
  public function updateConfig(string $config_name, FileStorage $file_storage, array $field_names = []) {
    // Load configuration.
    $config_values = $file_storage->read($config_name);
    $entity_type = $this->configManager->getEntityTypeIdByName($config_name);
    $config_entity_storage = $this->entityTypeManager->getStorage($entity_type);
    $config = $config_entity_storage->load($config_values['id']);

    if (empty($field_names)) {
      // Update the whole configuration.
      $config = $config_entity_storage->updateFromStorageRecord($config, $config_values);
    }
    else {
      // Update specific fields only.
      foreach ($field_names as $name) {
        $config->set($name, $config_values[$name]);
      }
    }
    $config->save();
  }

}
