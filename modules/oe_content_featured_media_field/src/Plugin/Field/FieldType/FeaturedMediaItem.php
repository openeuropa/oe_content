<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'oe_featured_media' field type.
 *
 * @FieldType(
 *   id = "oe_featured_media",
 *   label = @Translation("Featured media"),
 *   module = "oe_content_featured_media_field",
 *   description = @Translation("Stores a featured media item and caption."),
 *   category = @Translation("OpenEuropa"),
 *   default_formatter = "oe_featured_media_label",
 *   default_widget = "oe_featured_media_autocomplete",
 *   column_groups = {
 *     "target_id" = {
 *       "label" = @Translation("Media item"),
 *       "translatable" = FALSE
 *     },
 *     "caption" = {
 *       "label" = @Translation("Caption"),
 *       "translatable" = TRUE
 *     },
 *   },
 *   list_class = "\Drupal\Core\Field\EntityReferenceFieldItemList"
 * )
 */
class FeaturedMediaItem extends EntityReferenceItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'target_type' => 'media',
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function getPreconfiguredOptions(): array {
    // We don't want to use this field with any other entity types so we don't
    // preconfigure anything here.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['caption'] = DataDefinition::create('string')
      ->setLabel(t('Caption'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['caption'] = [
      'description' => 'The caption for the featured media.',
      'type' => 'varchar',
      'length' => 255,
    ];

    return $schema;
  }

}
