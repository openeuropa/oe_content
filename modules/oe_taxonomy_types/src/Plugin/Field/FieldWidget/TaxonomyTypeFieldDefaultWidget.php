<?php

namespace Drupal\oe_taxonomy_types\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines the 'oe_taxonomy_type_field_default' field widget.
 *
 * @FieldWidget(
 *   id = "oe_taxonomy_type_field_default",
 *   label = @Translation("Taxonomy type field widget"),
 *   field_types = {"oe_taxonomy_type_field"},
 * )
 */
class TaxonomyTypeFieldDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
    ];

    return $element;
  }

}
