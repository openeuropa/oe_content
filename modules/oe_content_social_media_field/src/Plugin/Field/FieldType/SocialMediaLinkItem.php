<?php

declare(strict_types = 1);

namespace Drupal\oe_content_social_media_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TypedDataTrait;

/**
 * Plugin implementation of the 'social_media_link' field type.
 *
 * @FieldType(
 *   id = "social_media_link",
 *   label = @Translation("Social media link"),
 *   module = "oe_content_social_media_field",
 *   category = @Translation("OpenEuropa"),
 *   description = @Translation("Stores a social media link."),
 *   default_formatter = "social_media_link_formatter",
 *   default_widget = "social_media_link_widget",
 *   constraints = {"SocialMediaField" = {}}
 * )
 */
class SocialMediaLinkItem extends FieldItemBase {

  use TypedDataTrait;

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'type' => [
          'type' => 'varchar',
          'length' => 255,
        ],
        'url' => [
          'type' => 'varchar',
          'length' => 2048,
        ],
        'title' => [
          'type' => 'varchar',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'url' => [['url', 30]],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // We consider the field empty if both fields are empty.
    $url = $this->get('url')->getValue();
    $title = $this->get('title')->getValue();

    return ($title === NULL || $title === '') && ($url === NULL || $url === '');
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['type'] = DataDefinition::create('string')
      ->setLabel(t('Type'));

    $properties['url'] = DataDefinition::create('uri')
      ->setLabel(t('URL'));

    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Link title'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDefaultValue($notify = TRUE) {
    $this->setValue([
      'type' => '',
      'url' => '',
      'title' => '',
    ], $notify);

    return $this;
  }

}
