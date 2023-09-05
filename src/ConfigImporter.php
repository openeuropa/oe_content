<?php

declare(strict_types = 1);

namespace Drupal\oe_content;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;

/**
 * Utility class to import configurations from files.
 *
 * @todo Possibly move to a service.
 *
 * @internal
 */
class ConfigImporter {

  /**
   * Imports a list of configurations from a folder.
   *
   * The configuration entity types are determined automatically from the config
   * name.
   *
   * @param string $type
   *   Extension type. One of 'theme', 'module' or 'profile'.
   * @param string $extension
   *   The module where the folder is located.
   * @param string $path
   *   The relative path to the folder, inside the module.
   * @param array $config_names
   *   A list of config names, available in the specified folder.
   * @param bool $create_if_missing
   *   If the configuration entity should be created if not found. Defaults to
   *   TRUE.
   */
  public static function importMultiple(string $type, string $extension, string $path, array $config_names, bool $create_if_missing = TRUE): void {
    $storage = self::getStorage($type, $extension, $path);
    foreach ($config_names as $config_name) {
      self::doImportConfig($storage, $config_name, $create_if_missing);
    }
  }

  /**
   * Imports a single configuration from a folder.
   *
   * @param string $type
   *   Extension type. One of 'theme', 'module' or 'profile'.
   * @param string $extension
   *   The module where the folder is located.
   * @param string $path
   *   The relative path to the folder, inside the module.
   * @param string $config_name
   *   The config file name.
   * @param bool $create_if_missing
   *   If the configuration entity should be created if not found. Defaults to
   *   TRUE.
   */
  public static function importSingle(string $type, string $extension, string $path, string $config_name, bool $create_if_missing = TRUE): void {
    $storage = self::getStorage($type, $extension, $path);
    self::doImportConfig($storage, $config_name, $create_if_missing);
  }

  /**
   * Imports a single config from a storage.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   The config storage where the config is located.
   * @param string $name
   *   The config file name, without extension.
   * @param bool $create_if_missing
   *   If the configuration entity should be created if not found. Defaults to
   *   TRUE.
   *
   * @throws \LogicException
   *   Thrown when the file is not found, or if the config entity doesn't exist
   *   and creation is disabled.
   */
  protected static function doImportConfig(StorageInterface $storage, string $name, bool $create_if_missing = TRUE): void {
    $config_manager = \Drupal::service('config.manager');
    $entity_type_manager = \Drupal::entityTypeManager();

    $config = $storage->read($name);
    if (!$config) {
      throw new \LogicException(sprintf('The configuration file "%s" was not found in the storage.', $name));
    }

    $entity_type = $config_manager->getEntityTypeIdByName($name);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $entity_storage */
    $entity_storage = $entity_type_manager->getStorage($entity_type);
    $id_key = $entity_storage->getEntityType()->getKey('id');
    $entity = $entity_storage->load($config[$id_key]);
    if (!$entity instanceof ConfigEntityInterface) {
      if (!$create_if_missing) {
        throw new \LogicException(sprintf('The configuration entity "%s" was not found.', $config[$id_key]));
      }

      // When we create a new config, it usually means that we are also shipping
      // it in the config/install folder, so we want to make sure it gets the
      // hash so Drupal treats it as a shipped config. This means that it gets
      // exposed to be translated via the locale system as well.
      $config['_core']['default_config_hash'] = Crypt::hashBase64(serialize($config));
      $entity = $entity_storage->createFromStorageRecord($config);
      $entity->save();

      return;
    }

    $entity = $entity_storage->updateFromStorageRecord($entity, $config);
    $entity->save();
  }

  /**
   * Returns a config file storage pointing to a folder.
   *
   * @param string $type
   *   Extension type. One of 'theme', 'module' or 'profile'.
   * @param string $extension
   *   The module where the folder is located.
   * @param string $path
   *   The relative path to the folder, inside the module.
   *
   * @return \Drupal\Core\Config\StorageInterface
   *   The config file storage.
   */
  protected static function getStorage(string $type, string $extension, string $path): StorageInterface {
    return new FileStorage(\Drupal::service('extension.list.' . $type)->getPath($extension) . $path);
  }

}
