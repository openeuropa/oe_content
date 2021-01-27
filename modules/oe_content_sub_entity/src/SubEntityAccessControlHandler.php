<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Access controller for the sub entity.
 */
class SubEntityAccessControlHandler extends EntityAccessControlHandler implements EntityHandlerInterface {

  /**
   * Contains the configuration object factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a TranslatorAccessControlHandler object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config object factory.
   */
  public function __construct(EntityTypeInterface $entity_type, ConfigFactoryInterface $config_factory) {
    parent::__construct($entity_type);
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Allowed when the operation is not view or the status is true.
    if ($operation === 'view') {
      $access_result = AccessResult::allowedIf($entity->isPublished() || $account->hasPermission('view unpublished sub entities'));
    }
    else {
      $access_result = AccessResult::allowed();
    }
    if ($entity->getParentEntity() != NULL) {
      // Delete permission on the entity, should just depend on 'update'
      // access permissions on the parent.
      $operation = ($operation == 'delete') ? 'update' : $operation;
      $parent_access = $entity->getParentEntity()->access($operation, $account, TRUE);
      $access_result = $access_result->andIf($parent_access);
    }
    return $access_result;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Allow entities to be created in the context of entity forms.
    if (\Drupal::requestStack()->getCurrentRequest()->getRequestFormat() === 'html') {
      return AccessResult::allowed()->addCacheContexts(['request_format']);
    }
    return AccessResult::neutral()->addCacheContexts(['request_format']);
  }

}
