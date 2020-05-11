<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for vocabulary reference widget plugins.
 */
class VocabularyReferenceWidgetPluginBase extends PluginBase implements VocabularyReferenceWidgetInterface {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
