<?php

declare(strict_types=1);

namespace Drupal\oe_content;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Converts date-only datetime field storages to date and time storages.
 *
 * Date-only and date and time datetime fields share the same database schema
 * so the field storage setting can be changed even if the field has data. The
 * existing values, however, need to be updated to contain a time as well.
 */
class DateTimeFieldStorageConverter {

  /**
   * Constructs a DateTimeFieldStorageConverter.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager
   *   The entity field manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFieldManagerInterface $entityFieldManager,
    protected Connection $database,
  ) {}

  /**
   * Converts a date-only datetime field storage to date and time.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $field_name
   *   The field name.
   * @param string $time
   *   The time to add to the existing values, in the H:i:s format (UTC).
   */
  public function convertToDateTime(string $entity_type_id, string $field_name, string $time = '12:00:00'): void {
    $field_storage = FieldStorageConfig::loadByName($entity_type_id, $field_name);
    if (!$field_storage instanceof FieldStorageConfig || $field_storage->getType() !== 'datetime') {
      throw new \InvalidArgumentException(sprintf('The field %s.%s does not exist or is not a datetime field.', $entity_type_id, $field_name));
    }

    if ($field_storage->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATETIME) {
      return;
    }

    if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)) {
      throw new \InvalidArgumentException(sprintf('The time %s is not in the H:i:s format.', $time));
    }

    $storage = $this->entityTypeManager->getStorage($entity_type_id);
    if (!$storage instanceof SqlEntityStorageInterface) {
      throw new \InvalidArgumentException(sprintf('The entity type %s does not use SQL storage.', $entity_type_id));
    }

    $field_storage->setSetting('datetime_type', DateTimeItem::DATETIME_TYPE_DATETIME);
    $field_storage->save();

    $table_mapping = $storage->getTableMapping();
    $column = $table_mapping->getFieldColumnName($field_storage, 'value');
    foreach ($table_mapping->getAllFieldTableNames($field_name) as $table_name) {
      // Only update the date-only values, in case some already have a time.
      $this->database->update($table_name)
        ->expression($column, 'CONCAT(' . $column . ', :time)', [':time' => 'T' . $time])
        ->condition($column, '%T%', 'NOT LIKE')
        ->execute();
    }

    $storage->resetCache();
    $this->entityFieldManager->clearCachedFieldDefinitions();
  }

}
