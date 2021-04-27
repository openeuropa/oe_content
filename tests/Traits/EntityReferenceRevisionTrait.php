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
    // Transform titles to ids and maintain the comma separated format.
    $items = explode(',', $labels);
    $items = array_map('trim', $items);
    $ids = [];
    $revision_ids = [];
    foreach ($items as $item) {
      $found_entities = $handler->getReferenceableEntities($item);
      $entity = \Drupal::entityTypeManager()->getStorage($handler->getConfiguration()['target_type'])->load(key(reset($found_entities)));
      $ids[] = $entity->id();
      $revision_ids[] = $entity->getRevisionId();
    }

    // For revision reference field we have give the target_revision_id.
    return [
      "{$field_name}:target_id" => implode(',', $ids),
      "{$field_name}:target_revision_id" => implode(',', $revision_ids),
    ];
  }

}
