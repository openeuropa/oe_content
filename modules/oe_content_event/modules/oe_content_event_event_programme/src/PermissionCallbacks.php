<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event_event_programme;

use Drupal\oe_content_entity\PermissionCallbacksBase;

/**
 * Provides dynamic permissions for the Programme Item entity.
 */
class PermissionCallbacks extends PermissionCallbacksBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId(): string {
    return 'oe_event_programme';
  }

}
