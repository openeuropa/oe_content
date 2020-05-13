<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a taxonomy type entity type.
 */
interface TaxonomyTypeInterface extends ConfigEntityInterface {

  /**
   * @return string
   */
  public function getDescription(): ?string;

  /**
   * @return string
   */
  public function getHandler(): ?string;

  /**
   * @return array
   */
  public function getHandlerSettings(): array;

}
