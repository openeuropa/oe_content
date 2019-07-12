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
        'title' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'text' => [
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
    // We consider the field empty if either of these properties left empty.
    $title = $this->get('title')->getValue();
    $text = $this->get('text')->getValue();

    return $title === NULL || $title === '' || $text === NULL || $text === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Timeline title'));

    $properties['text'] = DataDefinition::create('string')
      ->setLabel(t('Timeline text'));

    $properties['format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'));

    $properties['text_processed'] = DataDefinition::create('string')
      ->setLabel(t('Processed text'))
      ->setDescription(t('The timeline text with the text format applied.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\text\TextProcessed')
      ->setSetting('text source', 'text')
      ->setInternal(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue(
      [
        'title' => '',
        'text' => '',
        'format' => filter_fallback_format(),
      ],
      $notify
    );

    return $this;
  }

}
