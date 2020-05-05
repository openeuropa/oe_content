<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for vocabulary reference handler plugins.
 */
abstract class VocabularyReferenceHandlerPluginBase extends PluginBase implements VocabularyReferenceHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
