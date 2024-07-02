<?php

declare(strict_types=1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
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
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class FeaturedMediaEntityBrowserWidget extends EntityReferenceBrowserWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    // Override some default settings that we don't want changed by the user.
    return [
      'field_widget_edit' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    // We only keep a few configuration options from the parent. The settings
    // form validation can also be removed as it doesn't affect our settings.
    $allowed = [
      'entity_browser',
      'open',
    ];

    foreach ($element as $name => $form_element) {
      if (!in_array($name, $allowed)) {
        unset($element[$name]);
      }
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
        // Setting access to FALSE will not save the weight value so we're
        // changing the element type to hidden.
        $elements[$key]['_weight']['#type'] = 'hidden';
      }
    }

    // Determine which delta values need to be required.
    $required = $this->fieldDefinition->isRequired();

    if (!$required) {
      // If the field is not marked as required, we don't need to do anything.
      return $elements;
    }

    // Keep track of the deltas which have a media value in them.
    $value_deltas = [];

    foreach (Element::children($elements) as $child) {
      if (!isset($elements[$child]['target_id'])) {
        continue;
      }

      // Unset the required state from the parent as we re-set it to the correct
      // delta.
      unset($elements[$child]['#required']);

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

      return $elements;
    }

    // Otherwise, the ones where we have media items become required.
    foreach ($value_deltas as $delta) {
      $elements[$delta]['#required'] = TRUE;
      if (isset($elements[$delta]['entity_browser'])) {
        $elements[$delta]['entity_browser']['#required'] = TRUE;
      }
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

    $selection_mode = $this->getSetting('selection_mode');

    $display_entity_browser = EntityBrowserElement::isEntityBrowserAvailable($selection_mode, 1, count($entities));

    $element += [
      '#id' => $details_id,
      '#type' => 'details',
      '#open' => !empty($entities) || $this->getSetting('open') || $this->entityBrowserValueUpdated,
      'target_id' => [
        '#type' => 'hidden',
        '#id' => $hidden_id,
        '#attributes' => [
          'id' => $hidden_id,
          'data-cardinality' => 1,
          'data-entity-browser-visible' => $display_entity_browser,
        ],
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
        '#submit' => [[get_class($this), 'updateEntityBrowserValue']],
        '#limit_validation_errors' => [array_merge($element['#field_parents'], [$this->fieldDefinition->getName()])],
        '#executes_submit_callback' => TRUE,
      ],
    ];

    // Enable entity browser if requirements for that are fulfilled.
    if ($display_entity_browser) {
      $persistent_data = $this->getPersistentData();

      $element['entity_browser'] = [
        '#type' => 'entity_browser',
        '#entity_browser' => $this->getSetting('entity_browser'),
        '#cardinality' => 1,
        '#selection_mode' => $selection_mode,
        '#default_value' => $entities,
        '#entity_browser_validators' => $persistent_data['validators'],
        '#widget_context' => $persistent_data['widget_context'],
        '#custom_hidden_id' => $hidden_id,
        '#process' => [
          [
            '\Drupal\entity_browser\Element\EntityBrowserElement',
            'processEntityBrowser',
          ],
          [get_called_class(), 'processEntityBrowser'],
        ],
      ];

      $element['target_id']['#attributes']['data-entity-browser-available'] = 1;
    }
    else {
      $element['target_id']['#attributes']['data-entity-browser-visible'] = 0;
    }

    $element['#attached']['library'][] = 'entity_browser/entity_reference';

    $field_parents = $element['#field_parents'];

    $element['current'] = $this->displayCurrentSelection($details_id, $field_parents, $entities);
    // Append the delta to the #name of the remove button because if the same
    // media entity is referenced, there will be no difference between
    // buttons.
    if ($element['current']['items']) {
      $element['current']['items'][0]['remove_button']['#name'] .= $delta;
    }

    $element['caption'] = [
      '#title' => $this->t('Caption'),
      '#description' => $this->t('The caption that goes with the referenced media.'),
      '#type' => 'textfield',
      '#default_value' => $items->offsetExists($delta) ? $items->get($delta)->caption : '',
      '#element_validate' => [[$this, 'validateCaptionField']],
    ];

    return $element;
  }

  /**
   * Submit callback for the entity browser media selection element.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function updateEntityBrowserValue(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#parents'], 0, -1);
    $array_parents = array_slice($triggering_element['#array_parents'], 0, -1);

    $values = $form_state->getValue($parents);

    $entities = empty($values['target_id']) ? [] : explode(' ', trim($values['target_id']));
    $value = [];
    if ($entities) {
      // We expect only 1 as it's considered a delta of 1.
      $entity = reset($entities);
      $value['target_id'] = $entity;
    }
    else {
      $value['target_id'] = NULL;
    }

    // Set new value for this widget in the form_state.
    $element = &NestedArray::getValue($form, $array_parents);
    $form_state->setValueForElement($element, $value);

    // Rebuild form.
    $form_state->setRebuild();
  }

  /**
   * Caption field validator.
   *
   * @param array $element
   *   The element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $complete_form
   *   The entire form.
   */
  public function validateCaptionField(array &$element, FormStateInterface $form_state, array &$complete_form): void {
    $triggering_element = $form_state->getTriggeringElement();
    if ($triggering_element && isset($triggering_element['#ajax'])) {
      return;
    }

    $parents = $element['#parents'];
    $caption = $form_state->getValue($parents);
    array_pop($parents);
    $target_id = $form_state->getValue(array_merge($parents, ['target_id']));
    if (empty($target_id) && $caption) {
      $form_state->setError($element, $this->t('Please either remove the caption or select a Media entity'));
    }
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
  protected function formElementItemsEntities(FieldItemListInterface $items, int $delta, array $element, FormStateInterface $form_state): array {
    $entities = [];
    $entity_type = $this->fieldDefinition->getFieldStorageDefinition()->getSetting('target_type');
    $entity_storage = $this->entityTypeManager->getStorage($entity_type);

    // Check if we have any values in the form state for this element.
    $element_path = array_merge($element['#field_parents'], [
      $this->fieldDefinition->getName(),
      $delta,
    ]);
    $input_value = NestedArray::getValue($form_state->getUserInput(), $element_path, $input_exists);
    $value_exists = NestedArray::keyExists($form_state->getValues(), $element_path);

    if (!$value_exists && $input_exists && !is_array($input_value['target_id'])) {
      // If we have a value for this element but it's NULL, it means it was
      // removed consciously so we don't want to default to the one in the
      // field items.
      $data = empty($input_value['target_id']) ? [] : explode(' ', trim($input_value['target_id']));
      if (!$data) {
        return $entities;
      }

      // Ensure we maintain existing form values when no removal is triggered.
      $values = [];
      foreach ($data as $data_item) {
        $values[]['target_id'] = explode(':', $data_item)[1];
      }
      $items->setValue($values);
      return $items->referencedEntities();
    }

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
        $this->entityBrowserValueUpdated = TRUE;
      }

      // Since we are using a delta, replace the second value after the field
      // name key with the current delta being requested.
      $parents[$field_name_key + 1] = $delta;

      if ($value = $form_state->getValue($parents)) {
        return EntityBrowserElement::processEntityIds($value);
      }

      return $entities;
    }

    // Determine if we are adding a new delta value.
    if ($triggering_element) {
      $last_parent = end($triggering_element['#parents']);
      $field_name = prev($triggering_element['#parents']);
      if ($last_parent === 'add_more' && $field_name === $this->fieldDefinition->getName()) {
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
  public function settingsSummary() {
    $summary = [];

    $entity_browser_id = $this->getSetting('entity_browser');
    if (empty($entity_browser_id)) {
      return [$this->t('No entity browser selected.')];
    }
    else {
      if ($browser = $this->entityTypeManager->getStorage('entity_browser')->load($entity_browser_id)) {
        $summary[] = $this->t('Entity browser: @browser', ['@browser' => $browser->label()]);
      }
      else {
        $this->messenger->addError($this->t('Missing entity browser!'));
        return [$this->t('Missing entity browser!')];
      }
    }

    $details = $this->getSetting('open') ? $this->t('open') : $this->t('closed');
    $summary[] = $this->t('The widget details are by default: @details', ['@details' => $details]);

    return $summary;
  }

}
