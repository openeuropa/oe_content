<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Helper trait to load entities by different criteria.
 */
trait EntityLoadingTrait {

  /**
   * Load an entity by type, label and, optionally, by bundle.
   *
   * @param string $entity_type
   *   Entity type ID.
   * @param string $label
   *   Entity label.
   * @param string $bundle
   *   Entity bundle ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity object, if any.
   */
  protected function loadEntityByLabel(string $entity_type, string $label, string $bundle = NULL): EntityInterface {
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $label_key = $storage->getEntityType()->getKey('label');
    $properties = [
      $label_key => $label,
    ];

    // If bundle is set then add it to the query properties.
    if ($bundle) {
      $bundle_key = $storage->getEntityType()->getKey('bundle');
      $properties[$bundle_key] = $bundle;
    }
    $entities = $storage->loadByProperties($properties);

    if (empty($entities)) {
      throw new \InvalidArgumentException("No '$entity_type' entity of type '$bundle' with label '$label' has been found.");
    }

    return reset($entities);
  }

  /**
   * Load an entity type definition by its label, if any.
   *
   * @param string $label
   *   Entity definition label.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   Entity definition object.
   */
  protected function loadDefinitionByLabel(string $label): EntityTypeInterface {
    foreach (\Drupal::entityTypeManager()->getDefinitions() as $definition) {
      if ($label === (string) $definition->getLabel()) {
        return $definition;
      }
    }

    throw new \InvalidArgumentException("No entity_type with label '$label' has been found.");
  }

}
