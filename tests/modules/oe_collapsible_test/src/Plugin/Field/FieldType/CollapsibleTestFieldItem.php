<?php

declare(strict_types=1);

namespace Drupal\oe_collapsible_test\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'collapsible_test' field type.
 */
#[FieldType(
  id: "collapsible_test_field",
  label: new TranslatableMarkup("Collapsible Test"),
  description: new TranslatableMarkup("Stores a test collapsible item."),
  category: "OpenEuropa",
  default_widget: "collapsible_test_widget",
  default_formatter: "collapsible_test_formatter",
  module: "oe_collapsible_test"
)]
class CollapsibleTestFieldItem extends FieldItemBase {

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
    // We consider the field empty if all the fields are empty.
    $title = $this->get('title')->getValue();
    $body = $this->get('body')->getValue();

    return ($title === NULL || $title === '') && ($body === NULL || $body === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title'));

    $properties['body'] = DataDefinition::create('string')
      ->setLabel(t('Body'));

    $properties['format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'));

    $properties['body_processed'] = DataDefinition::create('string')
      ->setLabel(t('Processed body'))
      ->setDescription(t('The body with the text format applied.'))
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
        'title' => '',
        'body' => '',
        'format' => \Drupal::config('filter.settings')->get('fallback_format'),
      ],
      $notify
    );

    return $this;
  }

}
