<?php

declare(strict_types = 1);

namespace Drupal\oe_content_sub_entity_person;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Updates a given node's 'oe_persons' field values to sub-entities.
 *
 * The created sub-entities will be assigned to 'oe_persons_reference' field.
 */
class PersonNodeUpdater {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new PersonNodeUpdater.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Updates the revisions of a node with new person values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   */
  public function updateNode(NodeInterface $node): void {
    if (!$node->hasField('oe_persons')) {
      return;
    }

    /** @var \Drupal\oe_content_sub_entity_person\Entity\PersonInterface $person */
    $person = $this->entityTypeManager->getStorage('oe_person')->create([
      'type' => 'oe_person',
    ]);

    $ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->allRevisions()
      ->condition('nid', $node->id())
      ->sort('vid')
      ->execute();

    $revisions = $this->entityTypeManager
      ->getStorage('node')
      ->loadMultipleRevisions(array_keys($ids));

    /** @var \Drupal\node\NodeInterface $revision */
    foreach ($revisions as $revision) {
      // If the field has no value we can skip the creation of new revision on
      // the sub-entity and leave the 'oe_persons_reference' field empty.
      if ($revision->get('oe_persons')->isEmpty()) {
        continue;
      }

      // Prevent "double migration".
      if (!$revision->get('oe_persons_reference')->isEmpty()) {
        continue;
      }

      // Get the value from the revision and add it to the sub entity field.
      $persons_value = $revision->get('oe_persons')->getValue();
      $person->set('oe_node_reference', $persons_value);
      if (!$person->isNew()) {
        $person->setNewRevision(TRUE);
        $person->isDefaultRevision($revision->isDefaultRevision());
      }
      $person->save();

      // Save the value over the same revision.
      $revision->set('oe_persons_reference', $person);
      $revision->setNewRevision(FALSE);
      $revision->setSyncing(TRUE);
      // We don't want the automatic version system to kick in and set a version
      // based on a wrong revision comparison.
      // @see EntityVersionWorkflowManager::updateEntityVersion()
      $revision->entity_version_no_update = TRUE;
      $revision->save();
    }
  }

}
