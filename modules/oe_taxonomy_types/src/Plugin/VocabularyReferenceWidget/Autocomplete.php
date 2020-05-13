<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types\Plugin\VocabularyReferenceWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\oe_taxonomy_types\TaxonomyTypeAssociationInterface;
use Drupal\oe_taxonomy_types\VocabularyReferenceWidgetPluginBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Autocomplete widget.
 *
 * @VocabularyReferenceWidget(
 *   id = "autocomplete",
 *   label = @Translation("Autocomplete")
 * )
 */
class Autocomplete extends VocabularyReferenceWidgetPluginBase {

  /**
   * {@inheritdoc}
   */
  public function form(TaxonomyTypeAssociationInterface $association, $value, array &$form, FormStateInterface $form_state): array {
    $element = parent::form($association, $value, $form, $form_state);

    $selection_handler = $this->getSelectionHandler($association);
    // @todo This is not the way selection handlers should be used.
    $storage = \Drupal::entityTypeManager()->getStorage($selection_handler->getConfiguration()['target_type']);
    $value = isset($value) ? $storage->load($value) : NULL;
    $element['target_id'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => $selection_handler->getConfiguration()['target_type'],
      '#selection_handler' => $selection_handler->getPluginId(),
      '#selection_settings' => $selection_handler->getConfiguration()['handler_settings'],
      // Entity reference field items are handling validation themselves via
      // the 'ValidReference' constraint.
      '#validate_reference' => FALSE,
      '#maxlength' => 1024,
      '#default_value' => $value,
    ];

    return $element;
  }

}
