<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Change the field-type of a given field that data structure is similar.
 *
 * This class is used to change the field type of date range fields in event
 * content type in order to maintain the same machine name and data while the
 * field is being recreated during update.
 */
class EventDateRangeFieldTypeChanger {

  /**
   * Change the field type of date field to daterange_timezone.
   *
   * @param string $field
   *   The machine name of the field.
   * @param array $new_field_storage
   *   The new field storage config to create.
   * @param array $new_field_config
   *   The field config to create.
   */
  public static function changeFieldType(string $field, array $new_field_storage, array $new_field_config): void {
    // Check if we have any records in the field data table.
    $database = \Drupal::database();
    $count = $database->select("node__$field", 'n')
      ->countQuery()
      ->execute()
      ->fetchField();
    if ($count > 0) {
      // Create a table based on the one we want to back up. We are using direct
      // SQL queries to rely on the database server performance instead of using
      // the API as well in the chain.
      $data_table = "node__$field";
      $backup_data_table = "_node__$field";
      $query_string = 'CREATE TABLE ' . $backup_data_table . ' LIKE ' . $data_table;
      $database->query($query_string);

      // Copy every record from the field table to the backup table.
      $query_string = 'INSERT ' . $backup_data_table . ' SELECT * FROM ' . $data_table;
      $database->query($query_string);

      // Now do the same for the field revisions table.
      $revision_table = "node_revision__$field";
      $backup_revision_table = "_node_revision__$field";
      $query_string = 'CREATE TABLE ' . $backup_revision_table . ' LIKE ' . $revision_table;
      $database->query($query_string);
      $query_string = 'INSERT ' . $backup_revision_table . ' SELECT * FROM ' . $revision_table;
      $database->query($query_string);
    }

    // Delete the field and purge field data if there is any left over.
    /** @var \Drupal\field\Entity\FieldConfig $field_config */
    $field_config = FieldConfig::load("node.oe_event.$field");
    $field_config->delete();
    field_purge_batch(50);

    // Save the new field.
    $new_field_storage = FieldStorageConfig::create($new_field_storage);
    $new_field_storage->save();
    $new_field_config = FieldConfig::create($new_field_config);
    $new_field_config->save();

    if ($count > 0) {
      // Now we need to account for an additional column for timezone, so we
      // change the schema of the backup table accordingly.
      $query_string = 'ALTER TABLE ' . $backup_data_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
      $database->query($query_string);
      $query_string = 'ALTER TABLE ' . $backup_revision_table . ' ADD COLUMN ' . $field . '_timezone VARCHAR(255)';
      $database->query($query_string);

      // Use the default site timezone if one is already configured, or fall
      // back to the system timezone.
      $default_timezone = \Drupal::config('system.date')->get('timezone.default') ?: @date_default_timezone_get();
      $query_string = 'UPDATE ' . $backup_data_table . ' SET ' . $field . '_timezone = \'' . $default_timezone . '\'';
      $database->query($query_string);
      $query_string = 'UPDATE ' . $backup_revision_table . ' SET ' . $field . '_timezone = \'' . $default_timezone . '\'';
      $database->query($query_string);

      // Restore existing data in the same field table.
      $query_string = 'INSERT ' . $data_table . ' SELECT * FROM ' . $backup_data_table;
      $database->query($query_string);
      $query_string = 'INSERT ' . $revision_table . ' SELECT * FROM ' . $backup_revision_table;
      $database->query($query_string);

      // Delete the backup tables from the database.
      $database->query('DROP TABLE ' . $backup_data_table);
      $database->query('DROP TABLE ' . $backup_revision_table);
    }
  }

}
