<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

/**
 * Interface for vocabulary reference handler plugin managers.
 */
interface VocabularyReferenceHandlerPluginManagerInterface {

  /**
   * Returns an array of plugins to be used as #options in forms.
   *
   * @return array
   *   A list of plugin labels, keyed by plugin ID.
   */
  public function getDefinitionsAsOptions(): array;

}
