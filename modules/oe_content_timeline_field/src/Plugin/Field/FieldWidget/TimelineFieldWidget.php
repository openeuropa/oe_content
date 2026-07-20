<?php

declare(strict_types=1);

namespace Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Attribute\FieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\oe_content_timeline_field\CollapsibleWidgetBase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Plugin implementation of the 'timeline_widget' widget.
 */
#[FieldWidget(
  id: 'timeline_widget',
  label: new TranslatableMarkup('Timeline widget'),
  field_types: ['timeline_field'],
)]
class TimelineFieldWidget extends CollapsibleWidgetBase {

  /**
   * {@inheritdoc}
   */
  protected function getElementSummary(array $values): string {
    $summary_values = [
      $values['label'] ?? '',
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
    $open_element['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $items[$delta]->label ?? '',
      '#size' => 60,
      '#maxlength' => 100,
      '#required' => FALSE,
    ];
    $open_element['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $items[$delta]->title ?? '',
      '#size' => 60,
      '#maxlength' => 255,
      '#required' => FALSE,
    ];
    $open_element['body'] = [
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
    $item['format'] = $item['body']['format'] ?? \Drupal::config('filter.settings')->get('fallback_format');
    $item['body'] = $item['body']['value'] ?? '';

    return $item;
  }

  /**
   * {@inheritdoc}
   */
  public static function transformItemToUserInput(array $item, array $form, FormStateInterface $form_state): array {
    $item['body'] = [
      'format' => $item['format'],
      'value' => $item['body'],
    ];
    unset($item['format']);

    return $item;
  }

  /**
   * {@inheritdoc}
   *
   * Override the parameters to use the form element labels.
   */
  public function flagErrors(FieldItemListInterface $items, ConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state) {
    /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
    foreach ($violations as $offset => $violation) {
      $initial_parameters = $violation->getParameters();
      $parameters = $initial_parameters;
      if (isset($initial_parameters['%label'])) {
        $parameters['%label'] = $this->t('Label');
      }
      if (isset($initial_parameters['%title'])) {
        $parameters['%title'] = $this->t('Title');
      }
      if (isset($initial_parameters['%body'])) {
        $parameters['%body'] = $this->t('Content');
      }

      // If no parameters were replaced, do not replace the existing violation.
      if ($initial_parameters === $parameters) {
        continue;
      }

      $violations->set($offset, new ConstraintViolation(
        // phpcs:ignore
        $this->t($violation->getMessageTemplate(), $parameters),
        $violation->getMessageTemplate(),
        $parameters,
        $violation->getRoot(),
        $violation->getPropertyPath(),
        $violation->getInvalidValue(),
        $violation->getPlural(),
        $violation->getCode()
      ));
    }

    parent::flagErrors($items, $violations, $form, $form_state);
  }

}
