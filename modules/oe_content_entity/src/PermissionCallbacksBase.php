<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_entity\Entity\EntityTypeBaseInterface;

/**
 * Provides dynamic permissions for corporate entities.
 */
abstract class PermissionCallbacksBase {

  use StringTranslationTrait;

  /**
   * Returns the entity type id.
   *
   * @return string
   *   The entity type id.
   */
  abstract protected function getEntityTypeId(): string;

  /**
   * Returns an array of entity permissions.
   */
  public function buildPermissions(): array {
    $perms = [];
    $entity_type_id = $this->getEntityTypeId();
    $entity_type_definition = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
    $entity_type_storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    $entity_bundle_storage = \Drupal::entityTypeManager()->getStorage($entity_type_definition->getBundleEntityType());
    $entity_type_label = $entity_type_storage->getEntityType()->getLabel()->getUntranslatedString();
    // Generate entity permissions.
    $perms += $this->entityPermissions($entity_type_id, $entity_type_label);

    $bundles = $entity_bundle_storage->loadMultiple();
    // Generate permissions for all entity types.
    foreach ($bundles as $type) {
      $perms += $this->entityTypePermissions($type, $entity_type_label);
    }

    return $perms;
  }

  /**
   * Returns a list of access permissions for a given entity.
   *
   * @param string $entity_type_id
   *   The entity type id.
   * @param string $entity_type_label
   *   The entity type label.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function entityPermissions(string $entity_type_id, string $entity_type_label): array {
    $params = ['%entity_type_name' => $entity_type_label];
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
   * @param \Drupal\oe_content_entity\Entity\EntityTypeBaseInterface $type
   *   The entity type.
   * @param string $entity_type_label
   *   The entity type label.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function entityTypePermissions(EntityTypeBaseInterface $type, string $entity_type_label): array {
    $type_id = $type->id();
    $params = [
      '%entity_type_name' => $entity_type_label,
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
