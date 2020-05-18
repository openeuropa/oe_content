<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\EntityReferenceAutocompleteWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'oe_featured_media_widget' widget.
 *
 * @FieldWidget(
 *   id = "oe_featured_media_widget",
 *   label = @Translation("Featured media"),
 *   description = @Translation("An autocomplete entity reference field and a text field."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaFieldWidget extends EntityReferenceAutocompleteWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $referenced_entities = $items->referencedEntities();

    $element['featured_media'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Featured media'),
    ];

    // Append the match operation to the selection settings.
    $selection_settings = $this->getFieldSetting('handler_settings') + [
      'match_operator' => $this->getSetting('match_operator'),
      'match_limit' => $this->getSetting('match_limit'),
    ];

    $element['featured_media']['target_id'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Media item'),
      '#description' => $this->getMediaReferenceHelpText($selection_settings),
      '#target_type' => $this->getFieldSetting('target_type'),
      '#selection_handler' => $this->getFieldSetting('handler'),
      '#selection_settings' => $selection_settings,
      // Entity reference field items are handling validation themselves via
      // the 'ValidReference' constraint.
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#default_value' => isset($referenced_entities[$delta]) ? $referenced_entities[$delta] : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#states' => [
        'required' => [
          ':input[name="' . $this->fieldDefinition->getName() . '[' . $delta . '][featured_media][caption]"]' => ['filled' => TRUE],
        ],
      ],
    ];
    $element['featured_media']['caption'] = [
      '#title' => $this->t('Caption'),
      '#type' => 'textarea',
      '#default_value' => $items[$delta]->caption,
      '#rows' => 2,
      '#weight' => $delta,
      '#attributes' => ['class' => ['js-text-full', 'text-full']],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $key => $value) {
      $values[$key]['target_id'] = $value['featured_media']['target_id'];
      $values[$key]['caption'] = $value['featured_media']['caption'];
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
      '#markup' => $this->t('Type part of the media name.'),
    ];

    $overview_url = Url::fromRoute('entity.media.collection');
    if ($overview_url->access()) {
      $help_text['description_wrapper']['media_list_link'] = [
        '#type' => 'item',
        '#markup' => $this->t('See the <a href=":list_url" target="_blank">media list</a> (opens a new window) to help locate media.', [':list_url' => $overview_url->toString()]),
      ];
    }

    $all_bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('media');
    $allowed_bundles = !empty($selection_settings['target_bundles']) ? $selection_settings['target_bundles'] : [];
    $bundle_labels = array_map(function ($bundle) use ($all_bundles) {
      return $all_bundles[$bundle]['label'];
    }, $allowed_bundles);

    if (!empty($bundle_labels)) {
      $help_text['description_wrapper']['allowed_types'] = [
        '#type' => 'item',
        '#markup' => t('Allowed media types: %types', ['%types' => implode(', ', $bundle_labels)]),
      ];
    }

    return $help_text;
  }

}
