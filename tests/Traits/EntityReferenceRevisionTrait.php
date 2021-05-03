<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Traits;

use Drupal\field\Entity\FieldConfig;

/**
 * Helper trait to handle entity reference revision fields in Behat tests.
 */
trait EntityReferenceRevisionTrait {

  /**
   * Get reference revision field in a multi-value, parsable format.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   Entity type machine name.
   * @param string $field_name
   *   Reference revision field name.
   * @param string $labels
   *   Entity labels, comma separated.
   *
   * @return array
   *   Pair of target_id and target_revision_id for given field.
   */
  protected function getReferenceRevisionField(string $entity_type, string $bundle, string $field_name, string $labels): array {
    $field_config = FieldConfig::loadByName($entity_type, $bundle, $field_name);
    /** @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionInterface $handler */
    $handler = \Drupal::service('plugin.manager.entity_reference_selection')->getSelectionHandler($field_config);
    $configuration = $handler->getConfiguration();
    $entity_type_manager = \Drupal::entityTypeManager();
    $target_entity_type = $entity_type_manager->getDefinition($configuration['target_type']);
    $query = $entity_type_manager->getStorage($target_entity_type->id())->getQuery();

    if ($target_entity_type->id() === 'skos_concept') {
      $concept_schemes = $configuration['concept_schemes'];
      if (!empty($concept_schemes)) {
        $group = $query->orConditionGroup()
          ->condition('in_scheme', $concept_schemes, 'IN')
          ->condition('top_concept_of', $concept_schemes, 'IN');
        $query->condition($group);
      }
    }
    else {
      if (!empty($configuration['target_bundles']) && is_array($configuration['target_bundles'])) {
        $query->condition($target_entity_type->getKey('bundle'), $configuration['target_bundles'], 'IN');
      }
    }
    // Transform titles to ids and maintain the comma separated format.
    $items = explode(',', $labels);
    $items = array_map('trim', $items);
    $ids = [];
    $revision_ids = [];
    foreach ($items as $item) {
      if ($label_key = $target_entity_type->getKey('label')) {
        $query_instance = clone $query;
        $query_instance->condition($label_key, $item, 'CONTAINS');
        $results = $query->execute();
        $entity = $entity_type_manager->getStorage($target_entity_type->id())->load(reset($results));
        $ids[] = $entity->id();
        $revision_ids[] = $entity->getRevisionId();
      }
    }

    // For revision reference field we have give the target_revision_id.
    return [
      "{$field_name}:target_id" => implode(',', $ids),
      "{$field_name}:target_revision_id" => implode(',', $revision_ids),
    ];
  }

}
