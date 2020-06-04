<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\entity_browser\Plugin\Field\FieldWidget\EntityReferenceBrowserWidget;

/**
 * Plugin implementation of the 'oe_featured_media_entity_browser' widget.
 *
 * @FieldWidget(
 *   id = "oe_featured_media_entity_browser",
 *   label = @Translation("Featured media entity browser"),
 *   description = @Translation("An entity reference browser widget with caption field."),
 *   provider = "entity_browser",
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaEntityBrowserWidget extends EntityReferenceBrowserWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#description'] = $this->getMediaReferenceHelpText();

    $element['caption'] = [
      '#title' => $this->t('Caption'),
      '#description' => $this->t('The caption that goes with the referenced media.'),
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->caption,
      '#required' => $element['#required'],
    ];

    return $element;
  }

  /**
   * Determines the entities used for the form element.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field item to extract the entities from.
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   The list of entities for the form element.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  protected function formElementEntities(FieldItemListInterface $items, array $element, FormStateInterface $form_state) {
    $entities = [];
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $entity_storage = $this->entityTypeManager->getStorage($entity_type);

    // Find IDs from target_id element (it stores selected entities in form).
    // This was added to help solve a really edge casey bug in IEF.
    if (($target_id_entities = $this->getEntitiesByTargetId($element, $form_state)) !== FALSE) {
      return $target_id_entities;
    }

    // Determine if we're submitting and if submit came from this widget.
    $is_relevant_submit = FALSE;
    if (($trigger = $form_state->getTriggeringElement())) {
      // Can be triggered by hidden target_id element or "Remove" button.
      $last_parent = end($trigger['#parents']);
      if (in_array($last_parent, [
        'target_id',
        'remove_button',
        'replace_button',
      ])) {
        $is_relevant_submit = TRUE;

        // In case there are more instances of this widget on the same page we
        // need to check if submit came from this instance.
        $field_name = $this->fieldDefinition->getName();
        $found = FALSE;
        foreach ($trigger['#parents'] as $key => $parent) {
          if ($parent === $field_name) {
            $found = TRUE;
          }
        }
        $is_relevant_submit &= $found && (array_slice($trigger['#parents'], 0, count($element['#field_parents'])) == $element['#field_parents']);
      }
    };

    if ($is_relevant_submit) {
      // Submit was triggered by hidden "target_id" element when entities were
      // added via entity browser.
      if (!empty($trigger['#ajax']['event']) && $trigger['#ajax']['event'] == 'entity_browser_value_updated') {
        $parents = $trigger['#parents'];
      }
      // Submit was triggered by one of the "Remove" buttons. We need to walk
      // few levels up to read value of "target_id" element.
      elseif ($trigger['#type'] == 'submit' && strpos($trigger['#name'], $this->fieldDefinition->getName() . '_remove_') === 0) {
        $parents = array_merge(array_slice($trigger['#parents'], 0, -static::$deleteDepth), ['target_id']);
      }

      if (isset($parents) && $value = $form_state->getValue($parents)) {
        $entities = EntityBrowserElement::processEntityIds($value);
        return $entities;
      }
      return $entities;
    }
    // IDs from a previous request might be saved in the form state.
    elseif ($form_state->has([
      'entity_browser_widget',
      $this->getFormStateKey($items),
    ])
    ) {
      $stored_ids = $form_state->get([
        'entity_browser_widget',
        $this->getFormStateKey($items),
      ]);
      $indexed_entities = $entity_storage->loadMultiple($stored_ids);

      // Selection can contain same entity multiple times. Since loadMultiple()
      // returns unique list of entities, it's necessary to recreate list of
      // entities in order to preserve selection of duplicated entities.
      foreach ($stored_ids as $entity_id) {
        if (isset($indexed_entities[$entity_id])) {
          $entities[] = $indexed_entities[$entity_id];
        }
      }
      return $entities;
    }
    // We are loading for for the first time so we need to load any existing
    // values that might already exist on the entity. Also, remove any leftover
    // data from removed entity references.
    else {
      foreach ($items as $item) {
        if (isset($item->target_id)) {
          $entity = $entity_storage->load($item->target_id);
          if (!empty($entity)) {
            $entities[] = $entity;
          }
        }
      }
      return $entities;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $entities = [];
    foreach ($values as $delta => $value) {
      if (!empty($value['target_id'])) {
        $entities[$delta] = [
          'target_id' => $value['target_id'],
          'caption' => $value['caption'],
        ];
      }
    }
    $return = [];
    foreach ($entities as $delta => $entity) {
      $return[$delta] = [
        'target_id' => explode(':', $entity['target_id'])[1],
        'caption' => $entity['caption'],
      ];
    }

    return $return;
  }

  /**
   * Gets extensive help text for media reference field.
   *
   * It provides useful information about the allowed media types to reference
   * and a link to the media item list to create new media items.
   *
   * @return mixed
   *   The render array containing help texts.
   */
  protected function getMediaReferenceHelpText(): array {
    $help_text['description_wrapper'] = [
      '#type' => 'container',
    ];

    $overview_url = Url::fromRoute('entity.media.collection');
    if ($overview_url->access()) {
      $help_text['description_wrapper']['media_list_link'] = [
        '#type' => 'item',
        '#markup' => $this->t('You can manage all the media items on <a href=":list_url" target="_blank">this page</a>.', [':list_url' => $overview_url->toString()]),
      ];
    }

    $target_bundles = $target_bundles = $this->fieldDefinition->getSetting('handler_settings')['target_bundles'];
    if (!empty($target_bundles)) {
      $bundle_labels = [];
      $media_types = $this->entityTypeManager->getStorage('media_type')->loadMultiple($target_bundles);

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
