<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the sub entity types.
 */
class SubEntityTypeAccessControlHandler extends EntityAccessControlHandler {

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
      // permission to access content.
      case 'view label':
        return AccessResult::allowedIfHasPermission($account, 'access content');

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
