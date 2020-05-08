<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface;

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

  /**
   * Returns the ID of the associated entity reference selection handler.
   *
   * @param array $configuration
   *   An array of plugin configuration.
   *
   * @return \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface
   *   The entity reference selection handler instance
   */
  public function getHandler(array $configuration = []): SelectionInterface;

}
