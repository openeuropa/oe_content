<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'timeline_widget' widget.
 *
 * @FieldWidget(
 *   id = "timeline_widget",
 *   label = @Translation("Timeline widget"),
 *   field_types = {
 *     "timeline_field"
 *   }
 * )
 */
class TimelineFieldWidget extends WidgetBase implements WidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $items[$delta]->label ?? NULL,
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => FALSE,
    ];
    $element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $items[$delta]->title ?? NULL,
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => FALSE,
    ];
    $element['body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#default_value' => $items[$delta]->body ?? NULL,
      '#rows' => 5,
      '#required' => FALSE,
      '#format' => isset($items[$delta]->format) ? $items[$delta]->format : filter_fallback_format(),
      '#base_type' => 'textarea',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $item['format'] = $item['body']['format'];
      $item['body'] = $item['body']['value'];
    }

    return $values;
  }

}
