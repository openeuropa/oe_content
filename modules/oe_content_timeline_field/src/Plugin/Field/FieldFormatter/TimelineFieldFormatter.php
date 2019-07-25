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
      'limit' => 0,
      'show_more' => t('Show full timeline'),
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return [
      'limit' => [
        '#type' => 'number',
        '#title' => $this->t('Timeline limit'),
        '#description' => $this->t('The number of items to show. Default is "0" to show all items.'),
        '#default_value' => $this->getSetting('limit'),
        '#size' => 2,
        '#min' => 0,
        '#step' => 1,
      ],
      'show_more' => [
        '#type' => 'textfield',
        '#title' => $this->t('Show more label'),
        '#description' => $this->t('Set the label of the "show more" button when the limit is used. Defaults to "Show full timeline" when no value is given.'),
        '#default_value' => $this->getSetting('show_more'),
      ],
    ] + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (count($items) === 0) {
      return [];
    }

    $elements = [
      '#theme' => 'timeline',
      '#items' => [],
      '#limit' => $this->getSetting('limit'),
      '#show_more' => $this->getSetting('show_more'),
    ];

    foreach ($items as $delta => $item) {
      $elements['#items'][$delta]['label'] = [
        '#plain_text' => $item->label,
      ];
      $elements['#items'][$delta]['title'] = [
        '#plain_text' => $item->title,
      ];

      // The ProcessedText element already handles cache context & tag bubbling.
      // @see \Drupal\filter\Element\ProcessedText::preRenderText()
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
