<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Hook\Scope;

/**
 * Scope for hook running before RawDrupalContext::parseEntityFields().
 */
class BeforeParseEntityFieldsScope extends ParseEntityFieldsScopeBase {

  /**
   * Scope name.
   */
  const NAME = 'before.parse.entity.field';

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return self::NAME;
  }

}
