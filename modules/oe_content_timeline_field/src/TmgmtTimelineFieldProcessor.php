<?php

declare(strict_types = 1);

namespace Drupal\oe_content_timeline_field;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\tmgmt_content\DefaultFieldProcessor;

/**
 * TMGMT field processor for the timeline field.
 */
class TmgmtTimelineFieldProcessor extends DefaultFieldProcessor {

  /**
   * {@inheritdoc}
   */
  public function extractTranslatableData(FieldItemListInterface $field) {
    $data = parent::extractTranslatableData($field);

    // Remove the #format from the columns which actually should not have a
    // text format.
    foreach ($data as $delta => &$value) {
      if (!is_numeric($delta)) {
        continue;
      }

      foreach (['label', 'title'] as $name) {
        if (isset($value[$name]['#format'])) {
          unset($value[$name]['#format']);
        }
      }
    }

    return $data;
  }

}
