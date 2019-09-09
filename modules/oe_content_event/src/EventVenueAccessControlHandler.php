<?php

declare(strict_types = 1);

namespace Drupal\oe_content_event;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Event venue entity.
 *
 * @see \Drupal\oe_content_event\Entity\EventVenue.
 */
class EventVenueAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\oe_content_event\Entity\EventVenueInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished event venue entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published event venue entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit event venue entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete event venue entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add event venue entities');
  }

}
