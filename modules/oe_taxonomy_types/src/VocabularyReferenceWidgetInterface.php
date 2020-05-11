<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

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

}
