<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

/**
 * Defines an interface for vocabulary reference handler plugins.
 */
interface VocabularyReferenceHandlerInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

}
