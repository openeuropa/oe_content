<?php

declare(strict_types=1);

namespace Drupal\oe_content;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Base class for collapsible multi-value widgets.
 */
abstract class CollapsibleWidgetBase extends WidgetBase implements WidgetInterface {

  /**
   * The element form is in "expanded" state.
   */
  const EDIT_MODE_OPEN = 'open';

  /**
   * The element form is in "collapsed" state.
   */
  const EDIT_MODE_CLOSED = 'closed';

  /**
   * The machine name of "Duplicate" button functionality.
   */
  const OPTION_DUPLICATE = 'duplicate';

  /**
   * The machine name of "Edit all" button functionality.
   */
  const OPTION_EDIT_ALL = 'edit_all';

  /**
   * The machine name of "Collapse all" button functionality.
   */
  const OPTION_COLLAPSE_ALL = 'collapse_all';

  /**
   * The machine name of "Add above" button functionality.
   */
  const OPTION_ADD_NEW_ITEM_BEFORE = 'add_new_item_before';

  /**
   * Action position in the widget footer.
   */
  const ACTION_POSITION_FOOTER = 'footer';

  /**
   * Action position in the table header section.
   */
  const ACTION_POSITION_HEADER = 'header';

  /**
   * Action position next to the item form.
   */
  const ACTION_POSITION_ITEM = 'item';

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $field_parents = $element['#field_parents'];

    // Get the widget state for the current element and initialize it.
    $widget_state = static::getWidgetState($field_parents, $field_name, $form_state);
    if (!isset($widget_state['items'][$delta]['mode'])) {
      // If the edit mode is not set, use the default one.
      // The only exception is when the widget is in translation mode.
      $widget_state['items'][$delta]['mode'] = $widget_state['is_translating'] ? static::EDIT_MODE_OPEN : $this->getSetting('edit_mode');
    }
    if (!isset($widget_state['items'][$delta]['values'])) {
      $widget_state['items'][$delta]['values'] = $items[$delta]->getValue();
    }
    $edit_mode = $widget_state['items'][$delta]['mode'];

    // Build the form path to the current element.
    $element_parents = $field_parents;
    $element_parents[] = $field_name;
    $element_parents[] = $delta;

    // Set up the wrapper element.
    $element_id_prefix = implode('-', $element_parents);
    $element_name_prefix = str_replace('-', '_', $element_id_prefix);
    $element_wrapper_id = Html::getUniqueId($element_id_prefix . '-element-wrapper');
    $element['#prefix'] = '<div id="' . $element_wrapper_id . '" class="collapsible-element">';
    $element['#suffix'] = '</div>';

    // Define widget actions and dropdown actions.
    $actions = [];
    $dropdown_actions = [];

    $dropdown_actions['remove_button'] = $this->prepareButton([
      '#value' => $this->t('Remove'),
      '#name' => $element_name_prefix . '_remove',
      '#submit' => [[get_class($this), 'deleteSubmit']],
      // Ignore all validation errors because deleting invalid items is allowed.
      '#limit_validation_errors' => [],
      '#delta' => $delta,
      '#dropdown' => TRUE,
      '#ajax' => [
        'callback' => [get_class($this), 'itemAjax'],
        'wrapper' => $widget_state['field_wrapper_id'],
      ],
    ]);

    if ($this->isAdditionalOptionEnabled(static::OPTION_DUPLICATE)) {
      $dropdown_actions['duplicate_button'] = $this->prepareButton([
        '#value' => $this->t('Duplicate'),
        '#name' => $element_name_prefix . '_duplicate',
        '#submit' => [[get_class($this), 'duplicateSubmit']],
        '#limit_validation_errors' => [$element_parents],
        '#delta' => $delta,
        '#dropdown' => TRUE,
        '#ajax' => [
          'callback' => [get_class($this), 'itemAjax'],
          'wrapper' => $widget_state['field_wrapper_id'],
        ],
      ]);
    }

    if ($this->isAdditionalOptionEnabled(static::OPTION_ADD_NEW_ITEM_BEFORE)) {
      $dropdown_actions['add_above_button'] = $this->prepareButton([
        '#value' => $this->t('Add above'),
        '#name' => $element_name_prefix . '_add_above',
        '#submit' => [[get_class($this), 'addAboveSubmit']],
        '#limit_validation_errors' => [$element_parents],
        '#delta' => $delta,
        '#dropdown' => TRUE,
        '#ajax' => [
          'callback' => [get_class($this), 'itemAjax'],
          'wrapper' => $widget_state['field_wrapper_id'],
        ],
      ]);
    }

    if ($edit_mode === static::EDIT_MODE_CLOSED) {
      // If the edit mode is "closed", only show the element summary
      // and action buttons.
      $actions['edit_button'] = $this->prepareButton([
        '#value' => $this->t('Edit'),
        '#name' => $element_name_prefix . '_edit',
        '#weight' => 1,
        '#submit' => [[get_class($this), 'changeEditModeSubmit']],
        '#target_mode' => static::EDIT_MODE_OPEN,
        '#limit_validation_errors' => [$element_parents],
        '#delta' => $delta,
        '#ajax' => [
          'callback' => [get_class($this), 'itemAjax'],
          'wrapper' => $widget_state['field_wrapper_id'],
        ],
      ]);

      $element['summary'] = [
        '#weight' => -1000,
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#value' => $this->getElementSummary($widget_state['items'][$delta]['values']),
        '#attributes' => ['class' => 'collapsible-summary'],
      ];
    }
    else {
      // If the edit mode is "open", show the form elements.
      $element += $this->getElementFormItems($items, $delta, $element, $form, $form_state);

      $actions['collapse_button'] = $this->prepareButton([
        '#value' => $this->t('Collapse'),
        '#name' => $element_name_prefix . '_collapse',
        '#weight' => 1,
        '#submit' => [[get_class($this), 'changeEditModeSubmit']],
        '#target_mode' => static::EDIT_MODE_CLOSED,
        '#limit_validation_errors' => [$element_parents],
        '#delta' => $delta,
        '#ajax' => [
          'callback' => [get_class($this), 'itemAjax'],
          'wrapper' => $widget_state['field_wrapper_id'],
        ],
      ]);
    }

    // Add actions to the right column.
    $element['_actions'] = [
      '#type' => 'collapsible_actions',
      '#field_header' => FALSE,
      'actions' => $actions,
      'dropdown_actions' => $dropdown_actions,
    ];

    static::setWidgetState($field_parents, $field_name, $form_state, $widget_state);

    return $element;
  }

  /**
   * Gets the summary of the collapsed element.
   *
   * @param array $values
   *   The values of the element.
   *
   * @return string
   *   The element summary as string.
   */
  abstract protected function getElementSummary(array $values): string;

  /**
   * Gets the form items for the element.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field item values.
   * @param int $delta
   *   The delta of the element.
   * @param array $element
   *   The element form array.
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form items for the element.
   */
  abstract protected function getElementFormItems(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array;

  /**
   * {@inheritdoc}
   */
  public function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $cardinality = $this->fieldDefinition->getFieldStorageDefinition()
      ->getCardinality();
    $field_parents = $form['#parents'];
    $widget_state = static::getWidgetState($field_parents, $field_name, $form_state);
    $items_count = $widget_state['items_count'];

    // Add a default item if there are no items yet.
    if ($items_count == 0) {
      $widget_state['items'][0] = [
        'mode' => static::EDIT_MODE_OPEN,
        'original_delta' => 1,
      ];
      $items_count = 1;
      $widget_state['items_count'] = $items_count;
    }

    $is_multiple = $this->fieldDefinition->getFieldStorageDefinition()
      ->isMultiple();

    $field_title = $this->fieldDefinition->getLabel();
    $description = FieldFilteredMarkup::create(\Drupal::token()
      ->replace($this->fieldDefinition->getDescription() ?? ''));

    $elements = [];
    $field_id_prefix = implode('-', array_merge($field_parents, [$field_name]));
    $field_name_prefix = str_replace('-', '_', $field_id_prefix);
    $field_wrapper_id = Html::getId($field_id_prefix . '-field-wrapper');

    $elements['#prefix'] = '<div id="' . $field_wrapper_id . '">';
    $elements['#suffix'] = '</div>';

    // These values are passed to formElement(), so they can be used
    // in the AJAX submit handlers.
    $widget_state['field_wrapper_id'] = $field_wrapper_id;
    $widget_state['is_translating'] = $this->checkIsTranslating($form_state, $items->getEntity());
    static::setWidgetState($field_parents, $field_name, $form_state, $widget_state);

    if ($items_count > 0) {
      for ($delta = 0; $delta < $items_count; $delta++) {
        // Add a new empty item if it doesn't exist yet at this delta.
        if (!isset($items[$delta])) {
          $items->appendItem();
        }

        // For multiple fields, we set these values for the parent element.
        $element = [
          '#title' => $is_multiple ? '' : $field_title,
          '#description' => $is_multiple ? '' : $description,
        ];
        $element = $this->formSingleElement($items, $delta, $element, $form, $form_state);

        if ($element) {
          // Input field for the delta (drag-n-drop reordering).
          if ($is_multiple) {
            // We name the element '_weight' to avoid clashing with elements
            // defined by widget.
            $element['_weight'] = [
              '#type' => 'weight',
              '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
              '#title_display' => 'invisible',
              '#delta' => $items_count,
              '#default_value' => $items[$delta]->_weight ?: $delta,
              '#weight' => 100,
            ];
          }

          $elements[$delta] = $element;
        }
      }
    }

    $elements += [
      '#theme' => 'field_multiple_value_form',
      '#element_validate' => [[$this, 'multipleElementValidate']],
      '#required' => $this->fieldDefinition->isRequired(),
      '#field_name' => $field_name,
      '#cardinality' => $cardinality,
      '#cardinality_multiple' => TRUE,
      '#max_delta' => $items_count - 1,
      '#title' => $field_title,
      '#description' => $description,
    ];

    // Check if "Edit all" and "Collapse all" buttons can be added.
    $widget_state = static::getWidgetState($field_parents, $field_name, $form_state);
    $collapsed_items_count = 0;
    $open_items_count = 0;
    foreach ($widget_state['items'] ?? [] as $item) {
      if ($item['mode'] === static::EDIT_MODE_OPEN) {
        $open_items_count++;
      }
      elseif ($item['mode'] === static::EDIT_MODE_CLOSED) {
        $collapsed_items_count++;
      }
    }

    // Show the group operations if there are more than one item.
    if ($collapsed_items_count + $open_items_count > 1) {
      if ($open_items_count > 0 && $this->isAdditionalOptionEnabled(static::OPTION_COLLAPSE_ALL)) {
        $elements['header_actions']['actions']['collapse_all'] = $this->prepareButton([
          '#value' => $this->t('Collapse all'),
          '#submit' => [[get_class($this), 'changeAllEditModeSubmit']],
          '#target_mode' => static::EDIT_MODE_CLOSED,
          '#name' => $field_name_prefix . '_collapse_all',
          '#limit_validation_errors' => [
            array_merge($field_parents, [$field_name]),
          ],
          '#ajax' => [
            'callback' => [get_class($this), 'allActionsAjax'],
            'wrapper' => $field_wrapper_id,
          ],
        ]);
      }

      if ($collapsed_items_count > 0 && $this->isAdditionalOptionEnabled(static::OPTION_EDIT_ALL)) {
        $edit_all_placement = empty($elements['header_actions']['actions']) ? 'actions' : 'dropdown_actions';
        $elements['header_actions'][$edit_all_placement]['edit_all'] = $this->prepareButton([
          '#value' => $this->t('Edit all'),
          '#submit' => [[get_class($this), 'changeAllEditModeSubmit']],
          '#target_mode' => static::EDIT_MODE_OPEN,
          '#name' => $field_name_prefix . '_edit_all',
          '#limit_validation_errors' => [
            array_merge($field_parents, [$field_name, 'edit_all']),
          ],
          '#dropdown' => $edit_all_placement === 'dropdown_actions',
          '#ajax' => [
            'callback' => [get_class($this), 'allActionsAjax'],
            'wrapper' => $field_wrapper_id,
          ],
        ]);
      }
    }

    // Add the header actions as a separate element.
    // They will be moved to the actual header in preprocess.
    // @see oe_content_preprocess_field_multiple_value_form()
    if (isset($elements['header_actions'])) {
      $elements['header_actions']['#type'] = 'collapsible_actions';
      $elements['header_actions']['#field_header'] = TRUE;
      $elements['header_actions']['_weight'] = [
        '#type' => 'weight',
        '#default_value' => -100,
      ];
    }

    if (($items_count < $cardinality || $cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) && !$form_state->isProgrammed()) {
      $elements['add_more']['#type'] = 'container';
      $elements['add_more']['#weight'] = 1;
      $elements['add_more']['button'] = $this->prepareButton([
        '#name' => $field_name_prefix . '_add_more',
        '#value' => $this->t('Add another item'),
        '#limit_validation_errors' => [
          array_merge($field_parents, [
            $this->fieldDefinition->getName(),
            'add_more',
          ]),
        ],
        '#submit' => [[get_class($this), 'addMoreSubmit']],
        '#ajax' => [
          'callback' => [get_class($this), 'addMoreAjax'],
          'wrapper' => $field_wrapper_id,
        ],
        '#attributes' => [
          'class' => ['field-add-more-submit'],
        ],
      ]);
    }

    return $elements;
  }

  /**
   * Handle the case when the field is required.
   *
   * @param array $elements
   *   The form elements.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array $form
   *   The form.
   */
  public function multipleElementValidate(array $elements, FormStateInterface $form_state, array $form): void {
    $field_name = $this->fieldDefinition->getName();
    $widget_state = static::getWidgetState($elements['#field_parents'], $field_name, $form_state);

    if ($elements['#required'] && $widget_state['items_count'] < 1) {
      $form_state->setError($elements, $this->t('@name field is required.', ['@name' => $this->fieldDefinition->getLabel()]));
    }

    static::setWidgetState($elements['#field_parents'], $field_name, $form_state, $widget_state);
  }

  /**
   * Get common submit element information for processing ajax submit handlers.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   * @param string $position
   *   Position of triggering element.
   *
   * @return array
   *   Submit element information.
   */
  public static function getSubmitElementInfo(array $form, FormStateInterface $form_state, string $position = self::ACTION_POSITION_FOOTER): array {
    $submit['button'] = $form_state->getTriggeringElement();

    // Go up in the form, to the widget's container.
    // This will be different for various action types.
    switch ($position) {
      case static::ACTION_POSITION_FOOTER:
        $submit['element_path'] = array_slice($submit['button']['#array_parents'], 0, -2);
        break;

      case static::ACTION_POSITION_HEADER:
        $submit['element_path'] = array_slice($submit['button']['#array_parents'], 0, -3);
        break;

      case static::ACTION_POSITION_ITEM:
        $submit['element_path'] = array_slice($submit['button']['#array_parents'], 0, -4);
        $delta = array_slice($submit['button']['#array_parents'], -4, -3);
        $submit['delta'] = $delta[0];
        break;
    }

    $submit['element'] = NestedArray::getValue($form, $submit['element_path']);
    $submit['field_name'] = $submit['element']['#field_name'];
    $submit['parents'] = $submit['element']['#field_parents'];
    $submit['field_path'] = array_merge($submit['parents'], [$submit['field_name']]);

    return $submit;
  }

  /**
   * Extends the button with some default options.
   *
   * @param array $button_base
   *   Button base render array.
   *
   * @return array
   *   Button render array.
   */
  public static function prepareButton(array $button_base): array {
    $button = $button_base + [
      '#type' => 'submit',
    ];

    if (isset($button['#ajax'])) {
      $button['#ajax'] += [
        'effect' => 'fade',
      ];
    }

    // Make the button smaller if it is put inside a dropdown.
    if (!empty($button['#dropdown'])) {
      if (!isset($button['#attributes']['class'])) {
        $button['#attributes']['class'] = [];
      }
      $button['#attributes']['class'][] = 'button--small';
    }

    return $button;
  }

  /**
   * {@inheritdoc}
   */
  public static function deleteSubmit(&$form, FormStateInterface $form_state) {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_ITEM);
    $widget_state = static::getWidgetState($submit['parents'], $submit['field_name'], $form_state);
    $delta = $submit['delta'];

    // Unset the element from the form state.
    $user_input = $form_state->getUserInput();
    $field_user_input = NestedArray::getValue($user_input, $submit['field_path'], $exists);
    if ($exists) {
      unset($field_user_input[$delta]);
    }
    unset($submit['element'][$delta]);
    unset($widget_state['items'][$delta]);
    unset($field_user_input['header_actions']);

    // Mark the item as deleted for the ::form() method.
    $widget_state['deleted_item'] = $delta;
    if ($widget_state['items_count'] > 0) {
      $widget_state['items_count']--;
    }

    // Change the weights of the remaining items.
    // Save old deltas for mapping.
    $weight = -1 * $widget_state['items_count'];
    foreach ($field_user_input as $key => &$item) {
      if ($item) {
        $item['_weight'] = $weight++;
        $item['_old_delta'] = $key;
      }
    }

    // Reset indices while keeping mappings with widget state.
    $input = array_values($field_user_input);
    $new_widget_items = [];
    foreach ($input as $new_key => &$value) {
      $old_key = $value['_old_delta'];
      if (isset($widget_state['items'][$old_key])) {
        $new_widget_items[$new_key] = $widget_state['items'][$old_key];
        unset($value['_old_delta']);
      }
    }
    $widget_state['items'] = $new_widget_items;

    // Save the values back to the form state.
    $user_input = $form_state->getUserInput();
    NestedArray::setValue($user_input, $submit['field_path'], $input);
    $form_state->setUserInput($user_input);
    static::setWidgetState($submit['parents'], $submit['field_name'], $form_state, $widget_state);
    NestedArray::setValue($form, $submit['element_path'], $submit['element']);

    $form_state->setRebuild();
  }

  /**
   * The submit handler for changing the edit mode of one element.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public static function changeEditModeSubmit(array $form, FormStateInterface $form_state): void {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_ITEM);
    $widget_state = static::getWidgetState($submit['parents'], $submit['field_name'], $form_state);

    $old_mode = $widget_state['items'][$submit['delta']]['mode'];
    $new_mode = $submit['button']['#target_mode'];
    $widget_state['items'][$submit['delta']]['mode'] = $new_mode;

    // When we collapse the item form, we need to save the current values
    // into the widget state.
    if ($old_mode === static::EDIT_MODE_OPEN && $new_mode === static::EDIT_MODE_CLOSED) {
      $form_state_values = NestedArray::getValue($form_state->getValues(), $submit['field_path']);
      unset($form_state_values[$submit['delta']]['top']);
      unset($form_state_values[$submit['delta']]['_weight']);
      $form_state_values[$submit['delta']] = static::transformUserInputToItem($form_state_values[$submit['delta']], $form, $form_state);
      $widget_state['items'][$submit['delta']]['values'] = $form_state_values[$submit['delta']];
    }

    static::setWidgetState($submit['parents'], $submit['field_name'], $form_state, $widget_state);

    $form_state->setRebuild();
  }

  /**
   * The submit handler for changing the edit mode of all elements.
   *
   * @param array $form
   *   Current form state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   */
  public static function changeAllEditModeSubmit(array $form, FormStateInterface $form_state): void {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_HEADER);
    $widget_state = static::getWidgetState($submit['parents'], $submit['field_name'], $form_state);

    // Change edit mode for each item.
    $new_mode = $submit['button']['#target_mode'];
    foreach ($widget_state['items'] as $delta => $item) {
      $old_mode = $widget_state['items'][$delta]['mode'];
      $widget_state['items'][$delta]['mode'] = $new_mode;

      // When we collapse the item form, we need to save the current values
      // into the widget state.
      if ($old_mode === static::EDIT_MODE_OPEN && $new_mode === static::EDIT_MODE_CLOSED) {
        $form_state_values = NestedArray::getValue($form_state->getValues(), $submit['field_path']);
        unset($form_state_values[$delta]['top']);
        unset($form_state_values[$delta]['_weight']);
        $form_state_values[$delta] = static::transformUserInputToItem($form_state_values[$delta], $form, $form_state);
        $widget_state['items'][$delta]['values'] = $form_state_values[$delta];
      }
    }

    static::setWidgetState($submit['parents'], $submit['field_name'], $form_state, $widget_state);
    $form_state->setRebuild();
  }

  /**
   * The submit handler for creating a duplicate of an item.
   *
   * @param array $form
   *   Current form state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   */
  public static function duplicateSubmit(array $form, FormStateInterface $form_state): void {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_ITEM);
    $widget_state = static::getWidgetState($submit['parents'], $submit['field_name'], $form_state);

    $original_delta = $submit['button']['#delta'];
    $form_state_values = NestedArray::getValue($form_state->getValues(), $submit['field_path']);
    $original_values = $form_state_values[$original_delta];

    static::insertNewElement($widget_state, $form_state, $submit['field_path'], $original_delta, 'after', $original_values);

    static::setWidgetState($submit['parents'], $submit['field_name'], $form_state, $widget_state);
    $form_state->setRebuild();
  }

  /**
   * The submit handler for adding a new item above current one.
   *
   * @param array $form
   *   Current form state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   */
  public static function addAboveSubmit(array $form, FormStateInterface $form_state): void {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_ITEM);
    $widget_state = static::getWidgetState($submit['parents'], $submit['field_name'], $form_state);

    static::insertNewElement($widget_state, $form_state, $submit['field_path'], $submit['button']['#delta'], 'before');

    static::setWidgetState($submit['parents'], $submit['field_name'], $form_state, $widget_state);
    $form_state->setRebuild();
  }

  /**
   * The submit handler for adding a new item below the last one.
   *
   * @param array $form
   *   Current form state.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Current form state.
   */
  public static function addMoreSubmit(array $form, FormStateInterface $form_state) {
    $submit = static::getSubmitElementInfo($form, $form_state);
    $widget_state = static::getWidgetState($submit['parents'], $submit['field_name'], $form_state);

    if ($widget_state['items_count'] < $submit['element']['#cardinality'] || $submit['element']['#cardinality'] == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED) {
      $field_path = array_merge($submit['element']['#field_parents'], [$submit['element']['#field_name']]);
      static::insertNewElement($widget_state, $form_state, $field_path);
    }

    static::setWidgetState($submit['parents'], $submit['field_name'], $form_state, $widget_state);

    $form_state->setRebuild();
  }

  /**
   * Ajax callback for item actions.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public static function itemAjax(array $form, FormStateInterface $form_state): array {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_ITEM);
    return $submit['element'];
  }

  /**
   * Ajax callback for group actions.
   *
   * @param array $form
   *   Form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   */
  public static function allActionsAjax(array $form, FormStateInterface $form_state): array {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_HEADER);
    return $submit['element'];
  }

  /**
   * {@inheritdoc}
   */
  public static function addMoreAjax(array $form, FormStateInterface $form_state) {
    $submit = static::getSubmitElementInfo($form, $form_state, static::ACTION_POSITION_FOOTER);
    return $submit['element'];
  }

  /**
   * Adds a new element relative to other element.
   *
   * @param array $widget_state
   *   Widget state as reference, so that it can be updated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $field_path
   *   Path to the field.
   * @param int|null $relative_delta
   *   Delta position in list of elements, where new item will be added.
   *   If NULL, the new item will be added at the end.
   * @param string $relative_position
   *   This can be 'before' or 'after' the $relative_delta element.
   * @param array $values
   *   Values to be added to the new element.
   *
   * @return int
   *   New delta of the added element.
   */
  protected static function insertNewElement(array &$widget_state, FormStateInterface $form_state, array $field_path, ?int $relative_delta = NULL, string $relative_position = 'after', array $values = []): int {
    // Always append a new element at the end of the items list.
    $new_delta = $widget_state['items_count'];

    // Increase the item counters.
    $widget_state['items_count']++;

    // We need to update the user input to make the changes visible.
    $user_input = $form_state->getUserInput();
    $field_input = NestedArray::getValue($user_input, $field_path);

    if (is_null($relative_delta)) {
      // If the $relative_delta is not specified, calculate the highest
      // possible weight to put the element the end of the list.
      $max_weight = 0;
      foreach ($field_input as $input) {
        if (isset($input['_weight']) && $input['_weight'] > $max_weight) {
          $max_weight = $input['_weight'];
        }
      }
      $new_weight = $max_weight + 1;
    }
    else {
      // If the $relative_delta is specified, we need to adjust other weights.
      $weight_offset = match ($relative_position) {
        'before' => 0,
        'after' => 1,
      };
      $new_weight = $field_input[$relative_delta]['_weight'] + $weight_offset;
      foreach ($field_input as &$value) {
        if (isset($value['_weight']) && $value['_weight'] >= $new_weight) {
          $value['_weight']++;
        }
      }
    }

    // Add the item to the form state.
    $field_input[$new_delta] = [
      '_weight' => $new_weight,
    ] + $values;
    NestedArray::setValue($user_input, $field_path, $field_input);
    $form_state->setUserInput($user_input);
    $widget_state['items'][$new_delta]['mode'] = static::EDIT_MODE_OPEN;

    return $new_delta;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $item = static::transformUserInputToItem($item, $form, $form_state);
    }

    return $values;
  }

  /**
   * Massages the input into the format expected for field item.
   *
   * @param array $item
   *   The submitted form values for a single item.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array of item values.
   */
  abstract public static function transformUserInputToItem(array $item, array $form, FormStateInterface $form_state): array;

  /**
   * Transforms back the field data to the user input.
   *
   * @param array $item
   *   The submitted form values for a single item.
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   An array of item values.
   */
  abstract public static function transformItemToUserInput(array $item, array $form, FormStateInterface $form_state): array;

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $widget_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
    $path = array_merge($form['#parents'], [$field_name]);
    $key_exists = NULL;

    $form_state_values = $form_state->getValues();
    $field_values = NestedArray::getValue($form_state_values, $path, $key_exists);
    $user_input_values = $form_state->getUserInput();
    $field_input = NestedArray::getValue($user_input_values, $path);

    if ($key_exists) {
      unset($field_values['header_actions']);

      // Extract the collapsed values from the widget state.
      // The parent method will take care of the rest.
      foreach ($widget_state['items'] as $delta => $widget_item) {
        if ($widget_item['mode'] === static::EDIT_MODE_CLOSED) {
          $transformed_values = static::transformItemToUserInput($widget_item['values'], $form, $form_state);
          if (isset($field_values[$delta])) {
            $field_values[$delta] = $transformed_values + $field_values[$delta];
          }
          if (isset($field_input[$delta])) {
            $field_input[$delta] = $transformed_values + $field_input[$delta];
          }
        }
      }

      // Modify form values that will be picked up by parent method.
      NestedArray::setValue($form_state_values, $path, $field_values);
      $form_state->setValues($form_state_values);

      // Modify user input that will populate the form values.
      NestedArray::setValue($user_input_values, $path, $field_input);
      $form_state->setUserInput($user_input_values);
    }

    parent::extractFormValues($items, $form, $form_state);
  }

  /**
   * Determine if widget is in translation.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param \Drupal\Core\Entity\ContentEntityInterface $host
   *   The host entity.
   *
   * @return bool
   *   TRUE if the widget is in translation, otherwise FALSE.
   */
  protected function checkIsTranslating(FormStateInterface $form_state, FieldableEntityInterface $host): bool {
    if (!$host->isTranslatable()) {
      return FALSE;
    }
    if (!$host->getEntityType()->hasKey('default_langcode')) {
      return FALSE;
    }
    $default_langcode_key = $host->getEntityType()->getKey('default_langcode');
    if (!$host->hasField($default_langcode_key)) {
      return FALSE;
    }

    // @see \Drupal\content_translation\Controller\ContentTranslationController.
    if (!empty($form_state->get('content_translation'))) {
      // Adding a translation.
      return TRUE;
    }
    $langcode = $form_state->get('langcode');
    if (isset($langcode) && $host->hasTranslation($langcode) && $host->getTranslation($langcode)
      ->get($default_langcode_key)->value == 0) {
      // Editing a translation.
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state): bool|array {
    $property_path_array = explode('.', $violation->getPropertyPath());
    array_shift($property_path_array);
    if (!empty($property_path_array) && $sub_element = NestedArray::getValue($element, $property_path_array)) {
      return $sub_element;
    }
    return parent::errorElement($element, $violation, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['edit_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Edit mode'),
      '#description' => $this->t('The mode the items are in by default.'),
      '#options' => $this->getSettingOptions('edit_mode'),
      '#default_value' => $this->getSetting('edit_mode'),
      '#required' => TRUE,
    ];
    $elements['additional_options'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Enable additional widget options'),
      '#options' => $this->getSettingOptions('additional_options'),
      '#default_value' => $this->getSetting('additional_options'),
      '#multiple' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'edit_mode' => static::EDIT_MODE_CLOSED,
      'additional_options' => [
        static::OPTION_DUPLICATE => static::OPTION_DUPLICATE,
        static::OPTION_COLLAPSE_ALL => static::OPTION_COLLAPSE_ALL,
        static::OPTION_EDIT_ALL => static::OPTION_EDIT_ALL,
        static::OPTION_ADD_NEW_ITEM_BEFORE => static::OPTION_ADD_NEW_ITEM_BEFORE,
      ],
    ];
  }

  /**
   * Returns select options for a plugin setting.
   *
   * This is done to allow ::settingsSummary() to access option labels.
   *
   * @param string $setting_name
   *   The name of the widget setting. Supported settings:
   *   - "edit_mode"
   *   - "additional_options".
   *
   * @return array|null
   *   An array of setting option usable as a value for a "#options" key.
   */
  protected function getSettingOptions(string $setting_name): ?array {
    switch ($setting_name) {
      case 'edit_mode':
        $options = [
          static::EDIT_MODE_OPEN => $this->t('Open'),
          static::EDIT_MODE_CLOSED => $this->t('Closed'),
        ];
        break;

      case 'additional_options':
        $options = [
          static::OPTION_DUPLICATE => $this->t('Duplicate'),
          static::OPTION_COLLAPSE_ALL => $this->t('Collapse all'),
          static::OPTION_EDIT_ALL => $this->t('Edit all'),
          static::OPTION_ADD_NEW_ITEM_BEFORE => $this->t('Add new item before'),
        ];
        break;
    }

    return $options ?? NULL;
  }

  /**
   * Checks if a widget additional option is enabled or not.
   *
   * @param string $additional_option
   *   Additional option name to check.
   *
   * @return bool
   *   TRUE if the additional option is enabled, otherwise FALSE.
   */
  protected function isAdditionalOptionEnabled(string $additional_option): bool {
    $additional_options = $this->getSetting('additional_options');
    if (!empty($additional_options[$additional_option])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $edit_mode = $this->getSettingOptions('edit_mode')[$this->getSetting('edit_mode')];
    $summary[] = $this->t('Edit mode: @edit_mode', ['@edit_mode' => $edit_mode]);

    $additional_options_labels = array_intersect_key($this->getSettingOptions('additional_options'), array_filter($this->getSetting('additional_options')));
    if (!empty($additional_options_labels)) {
      $summary[] = $this->t('Additional options: @labels', ['@labels' => implode(', ', $additional_options_labels)]);
    }

    return $summary;
  }

}
