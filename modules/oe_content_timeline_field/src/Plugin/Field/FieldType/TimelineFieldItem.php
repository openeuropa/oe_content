<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'timeline' field type.
 *
 * @FieldType(
 *   id = "timeline_field",
 *   label = @Translation("Timeline"),
 *   module = "oe_content_timeline_field",
 *   category = @Translation("OpenEuropa"),
 *   description = @Translation("Stores timeline and its items."),
 *   default_formatter = "timeline_formatter",
 *   default_widget = "timeline_widget"
 * )
 */
class TimelineFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'label' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
        'title' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'body' => [
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
    // We consider the field empty if the title is left empty.
    $title = $this->get('title')->getValue();

    return $title === NULL || $title === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['label'] = DataDefinition::create('string')
      ->setLabel(t('Timeline label'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Timeline title'));

    $properties['body'] = DataDefinition::create('string')
      ->setLabel(t('Timeline body'));

    $properties['format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'));

    $properties['body_processed'] = DataDefinition::create('string')
      ->setLabel(t('Processed body'))
      ->setDescription(t('The timeline body with the text format applied.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\text\TextProcessed')
      ->setSetting('text source', 'body')
      ->setInternal(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue(
      [
        'label' => '',
        'title' => '',
        'body' => '',
        'format' => filter_fallback_format(),
      ],
      $notify
    );

    return $this;
  }

}
