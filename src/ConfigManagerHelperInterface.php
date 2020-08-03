<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Core\Config\StorageInterface;

/**
 * Interface for ConfigManagerHelper.
 */
interface ConfigManagerHelperInterface {

  /**
   * Creates configuration from the *.yml file.
   *
   * @param string $config_name
   *   Config name (file name).
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   File storage instance.
   */
  public function createConfig(string $config_name, StorageInterface $storage): void;

  /**
   * Updates existing configuration using values from *.yml files.
   *
   * @param string $config_name
   *   Config name (file name).
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   File storage instance.
   * @param array $field_names
   *   List of fields that have to be updated. All config values will be updated
   *   if variable is empty.
   */
  public function updateConfig(string $config_name, StorageInterface $storage, array $field_names = []): void;

}
