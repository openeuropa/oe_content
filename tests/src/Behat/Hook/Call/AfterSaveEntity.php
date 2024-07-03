<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Call;

use Drupal\Tests\oe_content\Behat\Hook\Scope\AfterSaveEntityScope;

/**
 * Hook running after the entity is saved.
 */
class AfterSaveEntity extends EntityAwareHookBase {

  /**
   * AfterSaveEntity constructor.
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
    parent::__construct(AfterSaveEntityScope::NAME, $this->getFilterSting($entity_type, $bundle), $callable, $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'AfterSaveEntity';
  }

}
