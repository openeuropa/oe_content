<?php

namespace Drupal\oe_content_timeline_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'timeline_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "timeline_formatter",
 *   label = @Translation("Timeline"),
 *   field_types = {
 *     "timeline_field"
 *   }
 * )
 */
class TimelineFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'timeline_limit' => 0,
      'button_label' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'timeline_limit' => [
        '#type' => 'number',
        '#title' => $this->t('Timeline limit'),
        '#description' => $this->t('The number of items to show. Default is "0" to show all items.'),
        '#default_value' => $this->getSetting('timeline_limit'),
        '#size' => 2,
        '#min' => 0,
        '#step' => 1,
        '#required' => FALSE,
      ],
      'button_label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Button label'),
        '#description' => $this->t('Set the label of the button to show all items when the limit is used. Default is "Show all timeline" when no value is given.'),
        '#default_value' => $this->getSetting('button_label'),
      ],
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements[$delta]['title'] = [
        '#type' => 'processed_text',
        '#text' => $item->title,
        '#langcode' => $item->getLangcode(),
      ];
      $elements[$delta]['text'] = [
        '#type' => 'processed_text',
        '#text' => $item->text,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
    }

    return $elements;
  }

}
