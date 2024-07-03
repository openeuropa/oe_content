<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Traits;

/**
 * Traits implementing methods required by EntityAwareInterface.
 *
 * This helps with composition.
 *
 * @see \Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareInterface
 */
trait EntityAwareTrait {

  /**
   * Entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * Entity bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * Get entity type.
   *
   * @return string
   *   The entity type machine name.
   */
  public function getEntityType(): string {
    return $this->entityType;
  }

  /**
   * Get entity bundle.
   *
   * @return string
   *   The entity bundle machine name.
   */
  public function getBundle(): string {
    return $this->bundle;
  }

}
