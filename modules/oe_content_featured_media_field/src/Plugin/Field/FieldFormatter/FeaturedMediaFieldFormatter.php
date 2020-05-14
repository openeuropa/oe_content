<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'Featured media as label' formatter.
 *
 * This is a default formatter for 'oe_featured_media' field type.
 * In oe_theme the formatter won't be used, we take care of the rendering
 * through preprocess hooks in a content type companion module.
 *
 * @FieldFormatter(
 *   id = "oe_featured_media_label",
 *   label = @Translation("Label"),
 *   description = @Translation("Display the label of the referenced media entity and the caption."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaFieldFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $parent_elements = parent::viewElements($items, $langcode);

    foreach ($parent_elements as $delta => $parent_element) {
      $elements[$delta]['featured_media'] = $parent_element;
      // Simply add the caption as the next element after the media link.
      $elements[]['caption'] = [
        '#plain_text' => $items[$delta]->caption,
      ];
    }

    return $elements;
  }

}
