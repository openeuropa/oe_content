<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity_contact;

use Drupal\oe_content_entity\PermissionCallbacksBase;
use Drupal\oe_content_entity_contact\Entity\ContactType;

/**
 * Provides dynamic permissions for Contact entity.
 */
class PermissionCallbacks extends PermissionCallbacksBase {

  /**
   * Returns the entity type id.
   *
   * @return string
   *   The entity type id.
   */
  protected function getEntityTypeId(): string {
    return 'oe_contact';
  }

  /**
   * Returns the entity type label.
   *
   * @return string
   *   The entity type label.
   */
  protected function getEntityTypeLabel(): string {
    return 'Contact';
  }

  /**
   * Returns the bundles of the entity.
   *
   * @return array
   *   The bundles.
   */
  protected function getBundles(): array {
    return ContactType::loadMultiple();
  }

}
