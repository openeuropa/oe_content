<?php

declare(strict_types = 1);

namespace Drupal\oe_content_social_media_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'social_media_link_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "social_media_link_formatter",
 *   label = @Translation("Social media link"),
 *   field_types = {
 *     "social_media_link"
 *   }
 * )
 */
class SocialMediaLinkFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (count($items) === 0) {
      return [];
    }

    $elements = [
      '#theme' => 'social_media_link',
      '#items' => [],
    ];

    foreach ($items as $delta => $item) {
      $elements['#items'][$delta]['type'] = [
        '#plain_text' => $item->type,
      ];
      $elements['#items'][$delta]['url'] = [
        '#plain_text' => $item->url,
      ];
      $elements['#items'][$delta]['title'] = [
        '#plain_text' => $item->title,
      ];
    }

    return $elements;
  }

}
