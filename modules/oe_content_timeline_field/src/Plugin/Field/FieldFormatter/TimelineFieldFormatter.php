<?php

declare(strict_types = 1);

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
      'show_more' => '',
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
      'show_more' => [
        '#type' => 'textfield',
        '#title' => $this->t('Show more label'),
        '#description' => $this->t('Set the label of the show more button when the limit is used. Default is "Show all timeline" when no value is given.'),
        '#default_value' => $this->getSetting('show_more'),
      ],
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [
      '#theme' => 'timeline',
      '#items' => [],
      '#timeline_limit' => $this->getSetting('timeline_limit'),
      '#show_more' => $this->getSetting('show_more'),
    ];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $elements['#items'][$delta]['title'] = [
        '#plain_text' => $item->title,
      ];
      $elements['#items'][$delta]['body'] = [
        '#type' => 'processed_text',
        '#text' => $item->body,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
    }

    return $elements;
  }

}
