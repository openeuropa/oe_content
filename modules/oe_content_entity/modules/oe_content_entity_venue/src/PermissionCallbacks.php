<?php

declare(strict_types=1);

namespace Drupal\oe_content_entity_venue;

use Drupal\oe_content_entity\PermissionCallbacksBase;

/**
 * Provides dynamic permissions for the Venue entity.
 */
class PermissionCallbacks extends PermissionCallbacksBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId(): string {
    return 'oe_venue';
  }

}
