<?php

declare(strict_types=1);

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
   * @param string|array $bundles
   *   Entity bundle ID, or array of entity bundle IDs.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity object, if any.
   */
  protected function loadEntityByLabel(string $entity_type, string $label, $bundles = NULL): EntityInterface {
    $bundles = (array) $bundles;
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    $label_key = $storage->getEntityType()->getKey('label');
    $properties = [
      $label_key => $label,
    ];

    // If bundle is set then add it to the query properties.
    if ($bundles) {
      $bundle_key = $storage->getEntityType()->getKey('bundle');
      $properties[$bundle_key] = $bundles;
    }
    $entities = $storage->loadByProperties($properties);

    if (empty($entities)) {
      $bundles = $bundles ?? 'of type ' . implode(', ', $bundles);
      throw new \InvalidArgumentException("No '$entity_type' entity {$bundles}with label '$label' has been found.");
    }

    return reset($entities);
  }

  /**
   * Load SKOS Concept entity by label and, optionally, by concept schemes.
   *
   * @param string $label
   *   Entity label.
   * @param string|array $concept_schemes
   *   A concept scheme, or a list of concept schemes.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity object, if any.
   */
  protected function loadSkosConceptEntityByLabel(string $label, $concept_schemes = NULL): EntityInterface {
    $concept_schemes = (array) $concept_schemes;
    $storage = \Drupal::entityTypeManager()->getStorage('skos_concept');
    $label_key = $storage->getEntityType()->getKey('label');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition($label_key, $label);

    // Restrict query per concept schemes, if any.
    if ($concept_schemes) {
      $group = $query->orConditionGroup()
        ->condition('in_scheme', $concept_schemes, 'IN')
        ->condition('top_concept_of', $concept_schemes, 'IN');
      $query->condition($group);
    }
    $result = $query->execute();
    $entities = $storage->loadMultiple($result);

    if (empty($entities)) {
      $concept_schemes = empty($concept_schemes) ? '' : 'with concept schemes ' . implode(', ', $concept_schemes) . ' ';
      throw new \InvalidArgumentException("No SKOS Concept entity {$concept_schemes}with label '$label' has been found.");
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
