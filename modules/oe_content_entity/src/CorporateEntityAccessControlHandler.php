<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;

/**
 * Defines the access control handler for the corporate entities.
 */
class CorporateEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $access = parent::checkAccess($entity, $operation, $account);
    if (!$access->isNeutral()) {
      return $access;
    }

    $entity_id = $entity->getEntityType()->id();
    $entity_bundle = $entity->bundle();
    switch ($operation) {
      case 'view':
        $permission = $entity->isPublished() ? 'view published ' . $entity_id : 'view unpublished ' . $entity_id;
        return AccessResult::allowedIfHasPermission($account, $permission)->addCacheableDependency($entity);

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit ' . $entity_bundle . ' corporate entity');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete ' . $entity_bundle . ' corporate entity');

      default:
        return AccessResult::neutral();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    $permissions = [
      $this->entityType->getAdminPermission(),
      'create ' . $entity_bundle . ' corporate entity',
    ];
    return AccessResult::allowedIfHasPermissions($account, $permissions, 'OR');
  }

}
