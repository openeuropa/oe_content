<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

/**
 * Scope for hook running after RawDrupalContext::parseEntityFields().
 */
class AfterParseEntityFieldsScope extends ParseEntityFieldsScopeBase {

  /**
   * Scope name.
   */
  const NAME = 'after.parse.entity.field';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return self::NAME;
  }

}
