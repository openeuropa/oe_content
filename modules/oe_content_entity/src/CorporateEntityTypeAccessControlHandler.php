<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;

/**
 * Defines the access control handler for the corporate entity types.
 */
class CorporateEntityTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected $viewLabelOperation = TRUE;

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      // We grant access to the 'view label' operation to all users having
      // permission to view published corporate entities.
      case 'view label':
        $corporate_entity_id = $entity->getEntityType()->getBundleOf();
        if ($corporate_entity_id) {
          return AccessResult::allowedIfHasPermission($account, "view published $corporate_entity_id");
        }
        return AccessResult::allowedIfHasPermission($account, 'access content');

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
