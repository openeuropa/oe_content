<?php

declare(strict_types = 1);

namespace Drupal\oe_content_entity;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oe_content_entity\Entity\CorporateEntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for providing dynamic permissions for corporate entities.
 *
 * Extending classes are responsible for providing permissions for the
 * corporate entities.
 */
abstract class PermissionCallbacksBase implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a PermissionCallbacksBase instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

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
    $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type_id);
    $entity_type_storage = $this->entityTypeManager->getStorage($entity_type_id);
    $entity_bundle_storage = $this->entityTypeManager->getStorage($entity_type_definition->getBundleEntityType());
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
   * @param \Drupal\oe_content_entity\Entity\CorporateEntityTypeInterface $bundle
   *   The bundle entity type.
   * @param string $entity_type_label
   *   The entity type label.
   *
   * @return array
   *   An associative array of permission names and descriptions.
   */
  protected function entityTypePermissions(CorporateEntityTypeInterface $bundle, string $entity_type_label): array {
    $bundle_id = $bundle->id();
    $entity_type_id = $bundle->getEntityType()->getBundleOf();
    $params = [
      '%entity_type_name' => $entity_type_label,
      '%bundle_name' => $bundle->label(),
    ];

    return [
      "create $entity_type_id $bundle_id corporate entity" => [
        'title' => $this->t('%entity_type_name: Create new %bundle_name entity', $params),
      ],
      "edit $entity_type_id $bundle_id corporate entity" => [
        'title' => $this->t('%entity_type_name: Edit any %bundle_name entity', $params),
      ],
      "delete $entity_type_id $bundle_id corporate entity" => [
        'title' => $this->t('%entity_type_name: Delete any %bundle_name entity', $params),
      ],
    ];
  }

}
