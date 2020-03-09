<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Hook\Call;

use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeSaveEntityScope;

/**
 * Hook running before the entity is saved.
 */
class BeforeSaveEntity extends EntityAwareHookBase {

  /**
   * BeforeSaveEntity constructor.
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
    parent::__construct(BeforeSaveEntityScope::NAME, $this->getFilterSting($entity_type, $bundle), $callable, $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'BeforeSaveEntity';
  }

}
