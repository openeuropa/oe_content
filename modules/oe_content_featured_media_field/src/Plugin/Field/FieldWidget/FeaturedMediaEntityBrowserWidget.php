<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
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
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    if (isset($element['field_widget_edit'])) {
      // Set default value to FALSE and do not allow access to change the
      // value as we do not allow to edit items.
      $element['field_widget_edit']['#default_value'] = FALSE;
      $element['field_widget_edit']['#access'] = FALSE;
    }
    if (isset($element['field_widget_replace'])) {
      // Set default value to FALSE and do not allow access to change the
      // value as we do not allow to replace items.
      $element['field_widget_replace']['#default_value'] = FALSE;
      $element['field_widget_replace']['#access'] = FALSE;
    }
    if (isset($element['selection_mode'])) {
      // Do not allow access to change the value as editing items would not be
      // possible.
      $element['selection_mode']['#access'] = FALSE;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $elements = parent::formMultipleElements($items, $form, $form_state);

    // Do not allow access to the weight select in order to avoid mixed data
    // between media items and captions.
    foreach (Element::children($elements) as $key) {
      if (isset($elements[$key]['_weight'])) {
        // Settings access false will not save the weight value so we're
        // changing the element type to hidden.
        $elements[$key]['_weight']['#type'] = 'hidden';
      }
    }

    // Determine which delta values need to be required.
    $required = $this->fieldDefinition->isRequired();

    if (!$required) {
      return $elements;
    }

    $sub_elements = [
      'target_id',
      'caption',
    ];

    $value_deltas = [];

    foreach (Element::children($elements) as $child) {
      if (!isset($elements[$child]['target_id'])) {
        continue;
      }

      // Unset the required state from the parent as we re-set it to the correct
      // delta.
      unset($elements[$child]['#required']);

      // Keep track of the deltas which have a media value.
      if (!empty($elements[$child]['current']['items'])) {
        $value_deltas[] = $child;
      }
    }

    // If there are no deltas with values, the first one becomes required.
    if (!$value_deltas) {
      $elements[0]['#required'] = TRUE;
      if (isset($elements[0]['entity_browser'])) {
        $elements[0]['entity_browser']['#required'] = TRUE;
      }

      $elements[0]['caption']['#required'] = TRUE;

      return $elements;
    }

    // Otherwise, only the ones where we have media items become/stay required.
    foreach ($value_deltas as $delta) {
      $elements[$delta]['#required'] = TRUE;
      if (isset($elements[$delta]['entity_browser'])) {
        $elements[$delta]['entity_browser']['#required'] = TRUE;
      }
      $elements[$delta]['caption']['#required'] = TRUE;
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   *
   * We need to extend this because the EntityReferenceBrowserWidget handles
   * multiple values and we do not because we need to have a caption. And
   * unfortunately it makes a call to
   * EntityReferenceBrowserWidget::formElementEntities which does not support
   * delta and which also needs to be overridden.
   *
   * For understanding the specificity of this method, check the comments in
   * the parent class.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // This returns only the entities for a given delta.
    $entities = $this->formElementItemsEntities($items, $delta, $element, $form_state);

    $hidden_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName() . '-target-id');
    $details_id = Html::getUniqueId('edit-' . $this->fieldDefinition->getName());

    $element += [
      '#id' => $details_id,
      '#type' => 'details',
      '#open' => !empty($entities) || $this->getSetting('open'),
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        '#attributes' => ['id' => $hidden_id],
        '#default_value' => implode(' ', array_map(
          function (EntityInterface $item) {
            return $item->getEntityTypeId() . ':' . $item->id();
          },
          $entities
        )),
        '#ajax' => [
          'callback' => [get_class($this), 'updateWidgetCallback'],
          'wrapper' => $details_id,
          'event' => 'entity_browser_value_updated',
        ],
      ],
    ];

    $selection_mode = $this->getSetting('selection_mode');

    // Enable entity browser if requirements for that are fulfilled.
    if (EntityBrowserElement::isEntityBrowserAvailable($selection_mode, 1, count($entities))) {
      $persistentData = $this->getPersistentData();

      $element['entity_browser'] = [
        '#type' => 'entity_browser',
        '#entity_browser' => $this->getSetting('entity_browser'),
        '#cardinality' => 1,
        '#selection_mode' => $selection_mode,
        '#default_value' => $entities,
        '#entity_browser_validators' => $persistentData['validators'],
        '#widget_context' => $persistentData['widget_context'],
        '#custom_hidden_id' => $hidden_id,
        '#process' => [
          ['\Drupal\entity_browser\Element\EntityBrowserElement', 'processEntityBrowser'],
          [get_called_class(), 'processEntityBrowser'],
        ],
      ];
    }

    $element['#attached']['library'][] = 'entity_browser/entity_reference';

    $field_parents = $element['#field_parents'];

    $element['current'] = $this->displayCurrentSelection($details_id, $field_parents, $entities);

    $element['caption'] = [
      '#title' => $this->t('Caption'),
      '#description' => $this->t('The caption that goes with the referenced media.'),
      '#type' => 'textfield',
      '#default_value' => $items->offsetExists($delta) ? $items->get($delta)->caption : '',
    ];

    return $element;
  }

  /**
   * Determines the entities used for the form element.
   *
   * This is mostly borrowed from
   * EntityReferenceBrowserWidget::formElementEntities() but accounts for the
   * fact that this widget does not handle multiple values so the delta
   * starts playing an important role.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field item to extract the entities from.
   * @param int $delta
   *   The order of the item.
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
  protected function formElementItemsEntities(FieldItemListInterface $items, int $delta, array $element, FormStateInterface $form_state) {
    $entities = [];
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $entity_storage = $this->entityTypeManager->getStorage($entity_type);

    // Determine if we're submitting and if submit came from this widget.
    $is_relevant_submit = FALSE;
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element) {
      $last_parent = end($triggering_element['#parents']);
      if (in_array($last_parent, [
        'target_id',
        'remove_button',
        'replace_button',
      ])) {
        $is_relevant_submit = TRUE;

        // In case there are more instances of this widget on the same page we
        // need to check if submit came from this instance.
        $field_name_key = end($triggering_element['#parents']) === 'target_id' ? 2 : static::$deleteDepth + 1;
        $field_name_key = count($triggering_element['#parents']) - $field_name_key;
        // Since we are using deltas as well, we go one level below.
        $field_name_key--;
        $is_relevant_submit &= ($triggering_element['#parents'][$field_name_key] === $this->fieldDefinition->getName()) &&
          (array_slice($triggering_element['#parents'], 0, count($element['#field_parents'])) == $element['#field_parents']);
      }
    };

    if ($is_relevant_submit) {
      // Submit was triggered by hidden "target_id" element when entities were
      // added via entity browser.
      if (!empty($triggering_element['#ajax']['event']) && $triggering_element['#ajax']['event'] == 'entity_browser_value_updated') {
        $parents = $triggering_element['#parents'];
      }
      // Submit was triggered by one of the "Remove" buttons. We need to walk
      // few levels up to read value of "target_id" element.
      elseif ($triggering_element['#type'] == 'submit' && strpos($triggering_element['#name'], $this->fieldDefinition->getName() . '_remove_') === 0) {
        $parents = array_merge(array_slice($triggering_element['#parents'], 0, -static::$deleteDepth), ['target_id']);
      }

      // Since we are using a delta, replace the second value after the field
      // name key with the current delta being requested.
      $parents[$field_name_key + 1] = $delta;

      if (isset($parents) && $value = $form_state->getValue($parents)) {
        return EntityBrowserElement::processEntityIds($value);
      }

      return $entities;
    }

    // Determine if we are adding a new delta value.
    if ($triggering_element) {
      $last_parent = end($triggering_element['#parents']);
      if ($last_parent === 'add_more') {
        $parents = $triggering_element['#parents'];
        // Remove the button key.
        array_pop($parents);
        $value = $form_state->getValue($parents);
        return EntityBrowserElement::processEntityIds($value[$delta]['target_id']);
      }
    }

    // We are loading for the first time so we need to load any existing
    // values that might already exist on the entity. Also, remove any leftover
    // data from removed entity references.
    if (isset($items[$delta]->target_id)) {
      $entity = $entity_storage->load($items[$delta]->target_id);
      if (!empty($entity)) {
        $entities[] = $entity;
      }
    }
    return $entities;
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
   * {@inheritdoc}
   */
  protected function displayCurrentSelection($details_id, array $field_parents, array $entities) {
    $current_selection = parent::displayCurrentSelection($details_id, $field_parents, $entities);

    foreach (Element::children($current_selection['items']) as $key) {
      // Do not allow access to edit and replace buttons.
      $current_selection['items'][$key]['edit_button']['#access'] = FALSE;
      $current_selection['items'][$key]['replace_button']['#access'] = FALSE;
    }

    return $current_selection;
  }

}
