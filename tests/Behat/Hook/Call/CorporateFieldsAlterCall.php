<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Hook\Call;

use Behat\Testwork\Hook\Scope\HookScope;
use Drupal\DrupalExtension\Hook\Call\EntityHook;
use Drupal\Tests\oe_content\Behat\Hook\Scope\CorporateFieldsAlterScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface;

/**
 * Runtime hook that allows to alter entity fields.
 */
class CorporateFieldsAlterCall extends EntityHook {

  /**
   * CorporateFieldsAlterCall constructor.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param array $callable
   *   Callable, i.e. the actual tagged context method.
   * @param string $description
   *   Call description.
   */
  public function __construct(string $entity_type, string $bundle, array $callable, string $description = '') {
    $filter_string = $entity_type . '.' . $bundle;
    parent::__construct(CorporateFieldsAlterScope::NAME, $filter_string, $callable, $description);
  }

  /**
   * {@inheritdoc}
   */
  public function filterMatches(HookScope $scope) {
    if ($scope instanceof EntityAwareHookScopeInterface) {
      return $scope->getEntityType() . '.' . $scope->getBundle() === $this->getFilterString();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'CorporateFieldsAlter';
  }

}
