<?php

declare(strict_types=1);

namespace Drupal\oe_collapsible_test\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\oe_content_timeline_field\CollapsibleWidgetBase;

/**
 * Plugin implementation of the 'collapsible_test_widget'.
 */
#[FieldWidget(
  id: 'collapsible_test_widget',
  label: new TranslatableMarkup('Collapsible test widget'),
  field_types: ['collapsible_test_field'],
)]
class CollapsibleTestFieldWidget extends CollapsibleWidgetBase {

  /**
   * {@inheritdoc}
   */
  protected function getElementSummary(array $values): string {
    $summary_values = [
      $values['title'] ?? '',
      strip_tags($values['body'] ?? ''),
    ];
    return implode(', ', array_filter($summary_values, fn($value) => trim($value) !== ''));
  }

  /**
   * {@inheritdoc}
   */
  protected function getElementFormItems(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $open_element = [];
    $open_element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $items[$delta]->title ?? '',
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => FALSE,
    ];
    $open_element['content'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Content'),
      '#default_value' => $items[$delta]->body ?? '',
      '#rows' => 5,
      '#required' => FALSE,
      '#format' => $items[$delta]->format ?? \Drupal::config('filter.settings')->get('fallback_format'),
      '#base_type' => 'textarea',
    ];
    return $open_element;
  }

  /**
   * {@inheritdoc}
   */
  public static function transformUserInputToItem(array $item, array $form, FormStateInterface $form_state): array {
    $item['format'] = $item['content']['format'] ?? \Drupal::config('filter.settings')->get('fallback_format');
    $item['body'] = $item['content']['value'] ?? '';
    unset($item['content']);

    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public static function transformItemToUserInput(array $item, array $form, FormStateInterface $form_state): array {
    $item['content']['format'] = $item['format'];
    $item['content']['value'] = $item['body'];
    unset($item['format'], $item['body']);

    return $item;
  }

}
