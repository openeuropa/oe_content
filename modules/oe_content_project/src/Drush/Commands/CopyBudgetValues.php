<?php

declare(strict_types = 1);

namespace Drupal\oe_content_project\Drush\Commands;

use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;

/**
 * Copies the project budget values from the old field to the new field.
 *
 * This command can be optionally executed to copy over values from old field.
 */
class CopyBudgetValues extends DrushCommands {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodestorage;

  /**
   * CopyBudgetValues class constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, MessengerInterface $messenger) {
    parent::__construct();

    $this->entityTypeManager = $entityTypeManager;
    $this->messenger = $messenger;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * Triggers the field value copy.
   *
   * @command oe-content:project-budget-copy-values
   */
  public function copyFieldValues(): void {
    $project_ids = $this->nodeStorage->getQuery()
      ->condition('type', 'oe_project')
      ->latestRevision()
      ->sort('nid')
      ->accessCheck(FALSE)
      ->execute();

    if (!$project_ids) {
      return;
    }

    $batch_builder = (new BatchBuilder())
      ->setTitle($this->t('Copy project budget field values'))
      ->setFinishCallback([$this, 'processFinished']);
    $batch_builder->addOperation([$this, 'processUpdate'], [
      $project_ids,
    ]);
    batch_set($batch_builder->toArray());
    drush_backend_batch_process();
  }

  /**
   * Batch operation to process the nodes.
   *
   * @param array $project_ids
   *   The project node IDs.
   * @param array|DrushBatchContext $context
   *   The batch context.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   * @SuppressWarnings(PHPMD.NPathComplexity)
   */
  public function processUpdate(array $project_ids, &$context): void {
    if (!isset($context['results']['processed'])) {
      $context['results']['processed'] = [];
      $context['results']['failed'] = [];
    }

    $sandbox = &$context['sandbox'];
    if (!$sandbox) {
      $sandbox['progress'] = 0;
      $sandbox['project_ids'] = array_unique($project_ids);
      $sandbox['max'] = count($sandbox['project_ids']);
    }

    // Process the project nodes.
    if ($sandbox['project_ids']) {
      $id = array_pop($sandbox['project_ids']);
      $this->updateNode($id);
      $context['results']['processed'][] = $id;
    }

    $sandbox['progress']++;

    $context['finished'] = $sandbox['progress'] / $sandbox['max'];
  }

  /**
   * Callback for when the batch processing completes.
   *
   * @param bool $success
   *   Whether the batch was successful.
   * @param array $results
   *   The batch results.
   */
  public function processFinished($success, array $results): void {
    if (!$success) {
      $this->messenger->addError($this->t('There was a problem with the batch.'));
      return;
    }

    $processed = count($results['processed']);
    if ($processed === 0) {
      $this->messenger->addStatus($this->t('No nodes have been processed.'));
    }
    else {
      $this->messenger->addStatus($this->t('@items nodes with values have been processed.', [
        '@items' => $processed,
      ]));
    }
  }

  /**
   * Update the project node and copy the budget field values on the node.
   *
   * @param mixed $id
   *   The ID of the project node.
   */
  public function updateNode(mixed $id): void {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $this->nodeStorage->loadRevision($this->nodeStorage->getLatestRevisionId($id));
    $changed = FALSE;
    $field_map = [
      'oe_project_budget' => 'oe_project_eu_budget',
      'oe_project_budget_eu' => 'oe_project_eu_contrib',
    ];
    foreach ($field_map as $source_field => $target_field) {
      if (!$node->get($source_field)->isEmpty()) {
        $node->set($target_field, $node->get($source_field)->value);
        $changed = TRUE;
      }
    }
    if ($changed) {
      $node->setNewRevision(FALSE);
      $node->setSyncing(TRUE);
      $node->save();
    }
  }

}
