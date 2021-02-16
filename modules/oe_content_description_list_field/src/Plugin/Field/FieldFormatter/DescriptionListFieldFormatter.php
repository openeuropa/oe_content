<?php

declare(strict_types = 1);

namespace Drupal\oe_content_description_list_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'Description list formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "description_list_formatter",
 *   label = @Translation("Description list formatter"),
 *   field_types = {
 *     "description_list_field"
 *   }
 * )
 */
class DescriptionListFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (count($items) === 0) {
      return [];
    }

    $elements = [
      '#theme' => 'description_list',
      '#items' => [],
    ];

    foreach ($items as $delta => $item) {
      $elements['#items'][$delta]['term'] = [
        '#plain_text' => $item->term,
      ];

      // The ProcessedText element already handles cache context & tag bubbling.
      // @see \Drupal\filter\Element\ProcessedText::preRenderText()
      $elements['#items'][$delta]['description'] = [
        '#type' => 'processed_text',
        '#text' => $item->description,
        '#format' => $item->format,
        '#langcode' => $item->getLangcode(),
      ];
    }

    return $elements;
  }

}
