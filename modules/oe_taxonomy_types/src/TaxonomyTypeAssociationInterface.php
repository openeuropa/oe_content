<?php

declare(strict_types = 1);

namespace Drupal\oe_taxonomy_types;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a taxonomy type association entity type.
 */
interface TaxonomyTypeAssociationInterface extends ConfigEntityInterface {

  /**
   * Value indicating an association accepts an unlimited number of values.
   */
  const CARDINALITY_UNLIMITED = -1;

  /**
   * @return string|null
   */
  public function getName(): ?string;

  /**
   * @return string|null
   */
  public function getField(): ?string;

  /**
   * @return string|null
   */
  public function getWidgetType(): ?string;

  /**
   * @return string|null
   */
  public function getTaxonomyType(): ?string;

  /**
   * @return int
   */
  public function getCardinality(): ?int;

  /**
   * @return bool|null
   */
  public function isRequired(): ?bool;

  /**
   * @return string|null
   */
  public function getPredicate(): ?string;

  /**
   * @return string|null
   */
  public function getHelpText(): ?string;

}
