<?php

declare(strict_types = 1);

namespace Drupal\oe_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Provides the EntityTypeBase class for content entity types.
 *
 * @ingroup oe_content_entity
 */
abstract class EntityTypeBase extends ConfigEntityBundleBase {

  /**
   * The machine name of the corporate content entity type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the corporate content entity type.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the corporate content entity type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description): EntityTypeBase {
    $this->description = $description;
    return $this;
  }

}
