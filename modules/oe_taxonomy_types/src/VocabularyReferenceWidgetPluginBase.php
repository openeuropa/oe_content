<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for vocabulary reference widget plugins.
 */
abstract class VocabularyReferenceWidgetPluginBase extends PluginBase implements VocabularyReferenceWidgetInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function form(TaxonomyTypeAssociationInterface $association, $value, array &$form, FormStateInterface $form_state): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getSelectionHandler(TaxonomyTypeAssociationInterface $association): SelectionInterface {
    $taxonomy_type = \Drupal::entityTypeManager()->getStorage('oe_taxonomy_type')->load($association->getTaxonomyType());
    $reference_manager = \Drupal::getContainer()->get('plugin.manager.oe_taxonomy_types.vocabulary_reference_handler');
    $reference_plugin = $reference_manager->createInstance($taxonomy_type->get('handler'));

    return $reference_plugin->getHandler($taxonomy_type->get('handler_settings'));
  }

}
