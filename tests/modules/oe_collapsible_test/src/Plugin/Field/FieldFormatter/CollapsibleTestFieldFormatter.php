<?php

declare(strict_types=1);

namespace Drupal\oe_collapsible_test\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'collapsible_test_formatter' formatter.
 */
#[FieldFormatter(
  id: 'collapsible_test_formatter',
  label: new TranslatableMarkup('Collapsible Test Formatter'),
  field_types: ['collapsible_test_field'],
)]
class CollapsibleTestFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    if (count($items) === 0) {
      return [];
    }

    $rows = [];
    foreach ($items as $delta => $item) {
      $rows[] = [
        $item->title,
        $item->body,
      ];
    }

    $elements = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Body'),
      ],
      '#rows' => $rows,
    ];

    return $elements;
  }

}
