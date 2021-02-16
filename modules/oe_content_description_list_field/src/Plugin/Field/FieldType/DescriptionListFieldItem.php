<?php

declare(strict_types = 1);

namespace Drupal\oe_content_description_list_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'Description list' field type.
 *
 * @FieldType(
 *   id = "description_list_field",
 *   label = @Translation("Description list"),
 *   module = "oe_content_description_list_field",
 *   category = @Translation("OpenEuropa"),
 *   description = @Translation("Stores an HTML description list element."),
 *   default_formatter = "description_list_formatter",
 *   default_widget = "description_list_widget"
 * )
 */
class DescriptionListFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'term' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'description' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'format' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'format' => ['format'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // We consider the field empty if all the fields are empty.
    $term = $this->get('term')->getValue();
    $description = $this->get('description')->getValue();

    return ($term === NULL || $term === '') && ($description === NULL || $description === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['term'] = DataDefinition::create('string')
      ->setLabel(t('Term'));

    $properties['description'] = DataDefinition::create('string')
      ->setLabel(t('Description'));

    $properties['format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'));

    $properties['description_processed'] = DataDefinition::create('string')
      ->setLabel(t('Processed description'))
      ->setDescription(t('The description element with the text format applied.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\text\TextProcessed')
      ->setSetting('text source', 'description')
      ->setInternal(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue(
      [
        'term' => '',
        'description' => '',
        'format' => filter_fallback_format(),
      ],
      $notify
    );

    return $this;
  }

}
