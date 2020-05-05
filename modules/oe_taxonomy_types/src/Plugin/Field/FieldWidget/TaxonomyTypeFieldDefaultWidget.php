<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_taxonomy_types\TaxonomyTypeAssociationInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Plugin\views\wizard\TaxonomyTerm;

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

    $values_by_association = [];
    foreach ($items->getValue() as $value) {
      $values_by_association[$value['target_association']][] = $value['target_id'];
    }

    foreach ($associations as $association) {
      $element[] = $this->getAssociationFormElement($association, $values_by_association[$association->id()] ?? [], $form, $form_state);
    }

    return $element;
  }

  protected function getAssociationFormElement(TaxonomyTypeAssociationInterface $association, array $values, array &$form, FormStateInterface $form_state): array {
    $element = [
      '#theme' => 'field_multiple_value_form',
      '#field_name' => $association->getName(),
      '#cardinality' => $association->getCardinality(),
      '#cardinality_multiple' => TRUE,
      '#required' => $association->isRequired(),
      '#title' => $association->label(),
      '#description' => $association->getHelpText(),
      '#association' => $association,
    ];

    // Determine the number of widgets to display.
    switch ($association->getCardinality()) {
      case TaxonomyTypeAssociationInterface::CARDINALITY_UNLIMITED:
        $max = count($values);
        break;

      default:
        $max = $association->getCardinality() - 1;
        break;
    }

    if (count($values) <= $max) {
      $values[] = NULL;
    }

    foreach ($values as $delta => $value) {
      $row = [];
      $row['target_association'] = [
        '#type' => 'value',
        '#value' => $association->id(),
      ];

      $value = isset($value) ? Term::load($value) : NULL;
      $row['target_id'] = [
        '#type' => 'entity_autocomplete',
        '#target_type' => 'taxonomy_term',
        '#selection_handler' => 'default:taxonomy_term',
        '#selection_settings' => [],
        // Entity reference field items are handling validation themselves via
        // the 'ValidReference' constraint.
        '#validate_reference' => FALSE,
        '#maxlength' => 1024,
        '#default_value' => $value,
      ];
      $row['_weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for row @number', ['@number' => $delta + 1]),
        '#title_display' => 'invisible',
        // Note: this 'delta' is the FAPI #type 'weight' element's property.
        '#delta' => $max,
        '#default_value' => $delta,
        '#weight' => 100,
      ];

      $element[] = $row;
    }

    $parents = $form['#parents'];
    $id_prefix = implode('-', array_merge($parents, [$association->id()]));
    $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');
    $element['#prefix'] = '<div id="' . $wrapper_id . '">';
    $element['#suffix'] = '</div>';

    $element['add_more'] = [
      '#type' => 'submit',
      '#name' => strtr($id_prefix, '-', '_') . '_add_more',
      '#value' => t('Add another item'),
      '#attributes' => ['class' => ['field-add-more-submit']],
      '#limit_validation_errors' => [],
      '#submit' => [[get_class($this), 'addMoreSubmit']],
      '#ajax' => [
        'callback' => [get_class($this), 'addMoreAjax'],
        'wrapper' => $wrapper_id,
        'effect' => 'fade',
      ],
    ];

    return $element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $flattened_values = [];
    foreach ($values as $value) {
      unset($value['add_more']);
      $flattened_values = array_merge($flattened_values, $value);
    }

    $flattened_values = array_filter($flattened_values, function ($value) {
      return !empty($value['target_id']);
    });

    return $flattened_values;
  }

  /**
   * @param string $field_id
   *
   * @return \Drupal\oe_taxonomy_types\Entity\TaxonomyTypeAssociation[]
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
