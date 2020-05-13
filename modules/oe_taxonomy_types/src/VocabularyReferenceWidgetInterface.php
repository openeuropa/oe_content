<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Defines an interface for vocabulary reference widget plugins.
 */
interface VocabularyReferenceWidgetInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

  /**
   * Returns the widget form.
   *
   * @param \Drupal\oe_taxonomy_types\TaxonomyTypeAssociationInterface $association
   *   The taxonomy type association entity.
   * @param array $value
   *   The current association values.
   * @param array $form
   *   An array representing the main form that the widget will be attached to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form element array.
   */
  public function form(TaxonomyTypeAssociationInterface $association, $value, array &$form, FormStateInterface $form_state): array;

  public function getSelectionHandler(TaxonomyTypeAssociationInterface $association): SelectionInterface;

}
