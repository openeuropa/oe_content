<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldFormatter;

use Drupal;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\media\Entity\Media;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalPhotoSource;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_content_featured_media_field\Plugin\Field\FieldType\FeaturedMediaItem;

/**
 * Plugin implementation of the 'Featured media as label' formatter.
 *
 * @FieldFormatter(
 *   id = "oe_featured_media_imageobjectvalue",
 *   label = @Translation("ImageValueObject for pattern"),
 *   description = @Translation("Return an object {path.alt,position} keys."),
 *   field_types = {
 *     "oe_featured_media"
 *   }
 * )
 */
class FeaturedMediaImageValueObjectFormatter extends FeaturedMediaFormatterBase {

  /**
   * {@inheritdoc}
   */
  function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = [];

    $parent_elements = parent::viewElements($items, $langcode);

    foreach ($parent_elements as $delta => $parent_element) {

      /** @var FeaturedMediaItem $item */
      $item = $items[$delta];

      /** @var \Drupal\media\Entity\Media $media */
      $media = Media::load($item->get('target_id')->getValue());
      // Retrieve the correct media translation.
      /** @var \Drupal\media\Entity\Media $media */
      $media = Drupal::service('entity.repository')->getTranslationFromContext($media, $langcode);

      // Get the media source.
      $source = $media->getSource();
      $is_image = $source instanceof MediaAvPortalPhotoSource || $source instanceof Image;
      // If it's not an image bail out.
      if (!$is_image) {
        continue;
      }

      $thumbnail = $media->get('thumbnail')->first();
      if($parent_element['#image_style']) {
        $element = ImageValueObject::fromStyledImageItem($thumbnail, $parent_element['#image_style']);
      }
      else {
        $element = ImageValueObject::fromImageItem($thumbnail);
      }
      $elements[$delta] = [
        '#theme' => 'pattern_imagevalueobject',
        '#image' => $element
      ];
    }
    return [
      '#image' => [
        'src' => $element->getSource()
      ],
    ];
    return ['#markup' => 'aaa'];
    return $elements;
  }

}
