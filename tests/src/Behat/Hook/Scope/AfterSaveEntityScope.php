<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

/**
 * Scope for hook running after saving an entity.
 */
class AfterSaveEntityScope extends SaveEntityScopeBase {

  /**
   * Scope name.
   */
  const NAME = 'after.save.entity';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return self::NAME;
  }

}
