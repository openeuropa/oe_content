<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Plugin\VocabularyReferenceWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_taxonomy_types\TaxonomyTypeAssociationInterface;
use Drupal\oe_taxonomy_types\VocabularyReferenceWidgetPluginBase;

/**
 * Select widget.
 *
 * @VocabularyReferenceWidget(
 *   id = "select",
 *   label = @Translation("Select list")
 * )
 */
class Select extends VocabularyReferenceWidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function form(TaxonomyTypeAssociationInterface $association, $value, array &$form, FormStateInterface $form_state): array {
    $element = parent::form($association, $value, $form, $form_state);

    $selection_handler = $this->getSelectionHandler($association);
    $entities = $selection_handler->getReferenceableEntities();

    // Rebuild the array by changing the bundle key into the bundle label.
    $target_type = $selection_handler->getConfiguration()['target_type'];
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($target_type);

    $options = [];
    foreach ($entities as $bundle => $entity_ids) {
      // The label does not need sanitizing since it is used as an optgroup
      // which is only supported by select elements and auto-escaped.
      $bundle_label = (string) $bundles[$bundle]['label'];
      $options[$bundle_label] = $entity_ids;
    }

    $element['target_id'] = [
      '#type' => 'select',
      '#options' => count($options) === 1 ? reset($options) : $options,
      '#default_value' => $value,
      '#empty_option' => $association->isRequired() ? $this->t('- Select -') : $this->t('- None -'),
    ];

    return $element;
  }

}
