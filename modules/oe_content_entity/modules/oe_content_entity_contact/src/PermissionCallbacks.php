<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_contact;

use Drupal\oe_content_entity\PermissionCallbacksBase;

/**
 * Provides dynamic permissions for the Contact entity.
 */
class PermissionCallbacks extends PermissionCallbacksBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityTypeId(): string {
    return 'oe_contact';
  }

}
