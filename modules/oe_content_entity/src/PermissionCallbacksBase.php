<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_entity\Entity\EntityTypeBase;

/**
 * Provides dynamic permissions for corporate entities.
 */
abstract class PermissionCallbacksBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  abstract protected function getEntityTypeId(): string;

  /**
   * {@inheritdoc}
   */
  abstract protected function getEntityTypeLabel(): string;

  /**
   * {@inheritdoc}
   */
  abstract protected function getBundles(): array;

  /**
   * Returns an array of entity permissions.
   */
  public function buildPermissions() {
    $perms = [];
    $entity_type_id = $this->getEntityTypeId();
    $entity_type_label = $this->getEntityTypeLabel();
    // Generate entity permissions.
    $perms += $this->entityPermissions($entity_type_id);

    $bundles = $this->getBundles();
    // Generate permissions for all entity types.
    foreach ($bundles as $type) {
      $perms += $this->entityTypePermissions($type);
    }

    return $perms;
  }

  /**
   * Returns a list of access permissions for a given entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function entityPermissions(string $entity_type_id) {
    $params = ['%entity_type_name' => $this->getEntityTypeLabel()];
    return [
      "access $entity_type_id overview" => [
        'title' => $this->t('%entity_type_name: Access overview page', $params),
      ],
      "access $entity_type_id canonical page" => [
        'title' => $this->t('%entity_type_name: Access canonical page', $params),
      ],
      "view published $entity_type_id" => [
        'title' => $this->t('%entity_type_name: View any published entity', $params),
      ],
      "view unpublished $entity_type_id" => [
        'title' => $this->t('%entity_type_name: View any unpublished entity', $params),
      ],
    ];
  }

  /**
   * Returns a list of CRUD permissions for a given corporate entity type.
   *
   * @param \Drupal\oe_content_entity\Entity\EntityTypeBase $type
   *   The entity type.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function entityTypePermissions(EntityTypeBase $type) {
    $type_id = $type->id();
    $params = [
      '%entity_type_name' => $this->getEntityTypeLabel(),
      '%type_name' => $type->label(),
    ];

    return [
      "create $type_id corporate entity" => [
        'title' => $this->t('%entity_type_name: Create new %type_name entity', $params),
      ],
      "edit $type_id corporate entity" => [
        'title' => $this->t('%entity_type_name: Edit any %type_name entity', $params),
      ],
      "delete $type_id corporate entity" => [
        'title' => $this->t('%entity_type_name: Delete any %type_name entity', $params),
      ],
    ];
  }

}
