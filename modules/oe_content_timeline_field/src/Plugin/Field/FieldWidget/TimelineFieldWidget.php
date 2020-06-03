<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_field\Plugin\Field\FieldWidget;

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

  /**
   * {@inheritdoc}
   */
  public function errorElement(array $element, ConstraintViolationInterface $violation, array $form, FormStateInterface $form_state) {
    $element = parent::errorElement($element, $violation, $form, $form_state);
    if ($violation) {
      if (empty($violation->arrayPropertyPath)) {
        return ($element === FALSE) ? FALSE : $element;
      }
    }
    return ($element === FALSE) ? FALSE : $element[$violation->arrayPropertyPath[0]];
  }

  /**
   * {@inheritdoc}
   *
   * Override the parameters to use the form element labels.
   */
  public function flagErrors(FieldItemListInterface $items, ConstraintViolationListInterface $violations, array $form, FormStateInterface $form_state) {
    /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
    foreach ($violations as $offset => $violation) {
      $parameters = $violation->getParameters();
      if (isset($parameters['%label'])) {
        $parameters['%label'] = $this->t('Label');
      }
      if (isset($parameters['%title'])) {
        $parameters['%title'] = $this->t('Title');
      }
      if (isset($parameters['%body'])) {
        $parameters['%body'] = $this->t('Content');
      }
      $violations->set($offset, new ConstraintViolation(
        // @codingStandardsIgnoreStart
        $this->t($violation->getMessageTemplate(), $parameters),
        // @codingStandardsIgnoreEnd
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
