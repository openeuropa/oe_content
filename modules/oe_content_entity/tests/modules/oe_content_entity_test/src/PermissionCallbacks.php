<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity_test;

use Drupal\oe_content_entity\PermissionCallbacksBase;

/**
 * Provides dynamic permissions for Corporate Test entity.
 */
class PermissionCallbacks extends PermissionCallbacksBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId(): string {
    return 'oe_corporate_entity_test';
  }

}
