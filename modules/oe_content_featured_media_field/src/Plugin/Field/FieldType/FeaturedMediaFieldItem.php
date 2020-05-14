<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\PreconfiguredFieldUiOptionsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * Plugin implementation of the 'oe_featured_media' field type.
 *
 * @FieldType(
 *   id = "oe_featured_media",
 *   label = @Translation("Featured media"),
 *   module = "oe_content_featured_media_field",
 *   category = @Translation("OpenEuropa"),
 *   description = @Translation("Stores a featured media item and caption."),
 *   default_formatter = "oe_featured_media_label",
 *   default_widget = "oe_featured_media_widget",
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
class FeaturedMediaFieldItem extends EntityReferenceItem implements OptionsProviderInterface, PreconfiguredFieldUiOptionsInterface {

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
    $element = parent::storageSettingsForm($form, $form_state, $has_data);
    // Don't allow any other entity type than media to be referenced.
    $element['target_type']['#default_value'] = 'media';
    $element['target_type']['#disabled'] = TRUE;

    return $element;
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
      'type' => 'varchar_ascii',
      'length' => 255,
    ];

    return $schema;
  }

}
