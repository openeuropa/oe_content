<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Call;

use Behat\Testwork\Hook\Scope\HookScope;
use Drupal\DrupalExtension\Hook\Call\EntityHook;
use Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface;

/**
 * Base class for runtime hook that are entity-aware.
 */
abstract class EntityAwareHookBase extends EntityHook {

  /**
   * {@inheritdoc}
   */
  public function filterMatches(HookScope $scope) {
    if ($scope instanceof EntityAwareHookScopeInterface) {
      return $scope->getEntityType() . '.' . $scope->getBundle() === $this->getFilterString();
    }
  }

  /**
   * Build filter string given entity type and bundle.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   *
   * @return string
   *   Filter string.
   */
  protected function getFilterSting(string $entity_type, string $bundle): string {
    return $entity_type . '.' . $bundle;
  }

}
