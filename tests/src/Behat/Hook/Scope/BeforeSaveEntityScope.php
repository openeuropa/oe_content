<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

/**
 * Scope for hook running before saving an entity.
 */
class BeforeSaveEntityScope extends SaveEntityScopeBase {

  /**
   * Scope name.
   */
  const NAME = 'before.save.entity';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return self::NAME;
  }

}
