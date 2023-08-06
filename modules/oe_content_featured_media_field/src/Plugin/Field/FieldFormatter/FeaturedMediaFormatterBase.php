<?php

declare(strict_types = 1);

namespace Drupal\oe_content_featured_media_field\Plugin\Field\FieldFormatter;

use Drupal;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\media\Plugin\media\Source\Image;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
use Drupal\media\Entity\Media;
use Drupal\media_avportal\Plugin\media\Source\MediaAvPortalPhotoSource;
use Drupal\oe_bootstrap_theme\ValueObject\ImageValueObject;
use Drupal\oe_content_featured_media_field\Plugin\Field\FieldType\FeaturedMediaItem;

/**
 * Base class for oe_featured_media formatters.
 */
abstract class FeaturedMediaFormatterBase extends ImageFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'display_caption' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['display_caption'] = [
      '#title' => $this->t('Display caption'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('display_caption'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $display_caption_setting = $this->getSetting('display_caption');
    if (!empty($display_caption_setting)) {
      $summary[] = t('Caption displayed');
    }

    return array_merge(parent::settingsSummary(), $summary);
  }

}
