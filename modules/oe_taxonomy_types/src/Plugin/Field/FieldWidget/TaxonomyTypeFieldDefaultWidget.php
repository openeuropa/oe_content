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
 *   multiple_values = TRUE
 * )
 */
class TaxonomyTypeFieldDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $associations = $this->getAssociations($this->fieldDefinition->id());

    foreach ($associations as $association) {
      $element['test'][] = [
        '#type' => 'inline_template',
        '#template' => '<div>Widget {{ widget }}, cardinality {{ cardinality }}, taxonomy type {{ taxonomy_type }}, predicate {{ predicate }}</div>',
        '#context' => [
          'widget' => $association->getWidgetType(),
          'cardinality' => $association->getCardinality(),
          'taxonomy_type' => $association->getTaxonomyType(),
          'predicate' => $association->getPredicate(),
        ],
      ];
    }

    return $element;
  }

  /**
   * @param string $field_id
   *
   * @return \Drupal\oe_taxonomy_types\Entity\OeTaxonomyTypeAssociation[]
   */
  protected function getAssociations(string $field_id): array {
    $storage = \Drupal::entityTypeManager()->getStorage('oe_taxonomy_type_association');
    $query = $storage->getQuery();
    $query->condition('field', $field_id);
    $results = $query->execute();

    if (empty($results)) {
      return [];
    }

    return $storage->loadMultiple($results);
  }

}
