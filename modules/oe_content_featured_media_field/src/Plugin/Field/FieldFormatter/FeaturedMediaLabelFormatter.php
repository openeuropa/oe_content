<?php

declare(strict_types=1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceLabelFormatter;

/**
 * Plugin implementation of the 'Featured media as label' formatter.
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
class FeaturedMediaLabelFormatter extends EntityReferenceLabelFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $parent_elements = parent::viewElements($items, $langcode);

    foreach ($parent_elements as $delta => $parent_element) {
      $elements[$delta]['featured_media'] = $parent_element;
      // Simply add the caption as the next element after the media link.
      $elements[$delta]['featured_media']['caption'] = [
        '#plain_text' => $items[$delta]->caption,
      ];
    }

    return $elements;
  }

}
