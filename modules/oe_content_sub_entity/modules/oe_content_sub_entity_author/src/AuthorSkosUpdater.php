<?php

declare(strict_types=1);

namespace Drupal\oe_content_sub_entity_author;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Updates a given node's 'oe_author' skos field values to sub-entities.
 *
 * The created sub-entities will be assigned to 'oe_authors' field.
 */
class AuthorSkosUpdater {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AuthorSkosUpdater.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Updates the revisions of a node with new author skos values.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node.
   */
  public function updateNode(NodeInterface $node): void {
    if (!$node->hasField('oe_author')) {
      return;
    }

    /** @var \Drupal\oe_content_sub_entity_author\Entity\AuthorInterface $author */
    $author = $this->entityTypeManager->getStorage('oe_author')->create([
      'type' => 'oe_corporate_body',
    ]);

    $ids = $this->entityTypeManager->getStorage('node')->getQuery()
      ->accessCheck(FALSE)
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
      // the sub-entity and leave the 'oe_authors' field empty.
      if ($revision->get('oe_author')->isEmpty()) {
        continue;
      }

      // Prevent "double migration".
      if (!$revision->get('oe_authors')->isEmpty()) {
        continue;
      }

      // Get the value from the revision and add it to the sub entity field.
      $author_value = $revision->get('oe_author')->getValue();
      $author->set('oe_skos_reference', $author_value);
      if (!$author->isNew()) {
        $author->setNewRevision(TRUE);
        $author->isDefaultRevision($revision->isDefaultRevision());
      }
      $author->save();

      // Save the value over the same revision.
      $revision->set('oe_authors', $author);
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
