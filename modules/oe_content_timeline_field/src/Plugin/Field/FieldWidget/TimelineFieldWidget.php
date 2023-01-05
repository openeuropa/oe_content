<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

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
      '#title' => $this->t('Content'),
      '#default_value' => $items[$delta]->body ?? NULL,
      '#rows' => 5,
      '#required' => FALSE,
      '#format' => $items[$delta]->format ?? filter_fallback_format(),
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

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    $property_path = $violation->arrayPropertyPath;
    if (!empty($property_path) && $sub_element = NestedArray::getValue($element, $property_path)) {
      return $sub_element;
    }
    return $element;
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
