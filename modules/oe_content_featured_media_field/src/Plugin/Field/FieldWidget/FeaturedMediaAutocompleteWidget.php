<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'oe_featured_media_autocomplete' widget.
 *
 * @FieldWidget(
 *   id = "oe_featured_media_autocomplete",
 *   label = @Translation("Featured media"),
 *   description = @Translation("An autocomplete entity reference field and a text field."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaAutocompleteWidget extends EntityReferenceAutocompleteWidget implements ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs FeaturedMediaAutocompleteWidget widget plugin.
   *
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['featured_media'] = [
      '#type' => 'fieldset',
    ];

    $element['featured_media'] += parent::formElement($items, $delta, $element, $form, $form_state);
    $element['featured_media']['#title'] = $element['featured_media']['target_id']['#title'];
    // Unset title_display to ensure the element title is always visible.
    unset($element['featured_media']['target_id']['#title_display']);
    $element['featured_media']['target_id']['#title'] = $this->t('Media item');
    $element['featured_media']['target_id']['#description'] = $this->getMediaReferenceHelpText($element['featured_media']['target_id']['#selection_settings']);

    $parents = $form['#parents'];
    if ($parents) {
      $first_parent = array_shift($parents);
      $name = $first_parent . '[' . implode('][', $parents) . '][' . $this->fieldDefinition->getName() . '][' . $delta . '][featured_media][caption]';
    }
    else {
      $name = $this->fieldDefinition->getName() . '[' . $delta . '][featured_media][caption]';
    }
    $element['featured_media']['target_id']['#states'] = [
      'required' => [
        ':input[name="' . $name . '"]' => ['filled' => TRUE],
      ],
    ];

    $element['featured_media']['caption'] = [
      '#title' => $this->t('Caption'),
      '#description' => $this->t('The caption that goes with the referenced media.'),
      '#type' => 'textarea',
      '#default_value' => $items[$delta]->caption,
      '#rows' => 2,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      $values[$delta]['target_id'] = $value['featured_media']['target_id'];
      $values[$delta]['caption'] = $value['featured_media']['caption'];
      unset($values[$delta]['featured_media']);
    }

    return $values;
  }

  /**
   * Gets extensive help text for media reference field.
   *
   * It provides useful information about the allowed media types to reference
   * and a link to the media item list to create new media items.
   *
   * @param array $selection_settings
   *   The entity reference field handler settings.
   *
   * @return mixed
   *   The render array containing help texts.
   */
  protected function getMediaReferenceHelpText(array $selection_settings): array {
    $help_text['description_wrapper'] = [
      '#type' => 'container',
    ];
    $help_text['description_wrapper']['default_text'] = [
      '#type' => 'item',
      '#markup' => $this->t('Start typing the name of the Media.'),
    ];

    $overview_url = Url::fromRoute('entity.media.collection');
    if ($overview_url->access()) {
      $help_text['description_wrapper']['media_list_link'] = [
        '#type' => 'item',
        '#markup' => $this->t('You can manage all the media items on <a href=":list_url" target="_blank">this page</a>.', [':list_url' => $overview_url->toString()]),
      ];
    }

    if (!empty($selection_settings['target_bundles'])) {
      $bundle_labels = [];
      $media_types = $this->entityTypeManager->getStorage('media_type')->loadMultiple($selection_settings['target_bundles']);

      foreach ($media_types as $media_type) {
        $bundle_labels[] = $media_type->label();
      }

      $help_text['description_wrapper']['allowed_types'] = [
        '#type' => 'item',
        '#markup' => $this->t('Allowed media types: %types', ['%types' => implode(', ', $bundle_labels)]),
      ];
    }

    return $help_text;
  }

}
