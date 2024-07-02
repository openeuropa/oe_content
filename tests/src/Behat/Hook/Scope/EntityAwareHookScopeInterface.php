<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

use Behat\Testwork\Hook\Scope\HookScope;

/**
 * Interface for entity-aware hook scopes.
 */
interface EntityAwareHookScopeInterface extends HookScope {

  /**
   * Get entity type.
   *
   * @return string
   *   The entity type machine name.
   */
  public function getEntityType(): string;

  /**
   * Get entity bundle.
   *
   * @return string
   *   The entity bundle machine name.
   */
  public function getBundle(): string;

}
