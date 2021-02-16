<?php

declare(strict_types = 1);

namespace Drupal\oe_content_description_list_field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

/**
 * Plugin implementation of the 'Description list widget' widget.
 *
 * @FieldWidget(
 *   id = "description_list_widget",
 *   label = @Translation("Description list widget"),
 *   field_types = {
 *     "description_list_field"
 *   }
 * )
 */
class DescriptionListFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['term'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Term'),
      '#default_value' => $items[$delta]->term ?? NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => FALSE,
    ];
    $element['description'] = [
      '#type' => 'text_format',
      '#base_type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $items[$delta]->description ?? NULL,
      '#format' => isset($items[$delta]->format) ? $items[$delta]->format : filter_fallback_format(),
      '#rows' => 5,
      '#required' => FALSE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $item['format'] = $item['description']['format'];
      $item['description'] = $item['description']['value'];
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    if (!empty($violation->arrayPropertyPath) && $sub_element = NestedArray::getValue($element, $violation->arrayPropertyPath)) {
      return $sub_element;
    }
    return $element;
  }

}
