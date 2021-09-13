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
    $corporate_entity_id = $entity->getEntityType()->getBundleOf();
    switch ($operation) {
      // We grant access to the 'view label' operation to all users having
      // permission to view published corporate entities.
      case 'view label':
        return AccessResult::allowedIfHasPermission($account, "view published $corporate_entity_id");

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
