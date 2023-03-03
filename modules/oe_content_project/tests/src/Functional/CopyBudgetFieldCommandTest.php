<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content_project\Functional;

use Drupal\Tests\BrowserTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests drush command project-budget-copy-values:run.
 *
 * @group batch1
 */
class CopyBudgetFieldCommandTest extends BrowserTestBase {

  use DrushTestTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'path',
    'oe_content_project',
  ];

  /**
   * Ensures drush command copies data from deprecated field to new field.
   */
  public function testUpdateProjectNodes(): void {
    $project_nodes = [];
    $storage = \Drupal::entityTypeManager()->getStorage('node');

    $values = [
      'title' => 'Project title 1',
      'type' => 'oe_project',
      'oe_project_budget' => 123456,
      'oe_project_budget_eu' => 654321,
      'moderation_state' => 'published',
    ];
    $project_node = $storage->create($values);
    $project_node->save();
    $project_nodes[] = $project_node;

    $values['title'] = 'Project title 2';
    $values['oe_project_budget'] = 123456;
    $project_node = $storage->create($values);
    $project_node->save();
    $project_nodes[] = $project_node;

    $values['title'] = 'Project title 3';
    $values['oe_project_budget_eu'] = 654321;
    $project_node = $storage->create($values);
    $project_node->save();
    $project_nodes[] = $project_node;

    $values['title'] = 'Project title 4';
    $values['oe_project_budget'] = NULL;
    $values['oe_project_budget_eu'] = NULL;
    $project_node = $storage->create($values);
    $project_node->save();
    $project_nodes[] = $project_node;

    $this->drush('project-budget-copy-values:run');
    $storage->resetCache();

    foreach ($project_nodes as $node) {
      // Assert we have only one revision after copy.
      $revision_ids = $storage->getQuery()
        ->allRevisions()
        ->condition('nid', $node->id())
        ->sort('vid')
        ->execute();
      $this->assertEquals(1, count($revision_ids));

      $node = $storage->load($node->id());

      // Assert the field values had been copied, and they are decimal format.
      if ($node->get('oe_project_budget')->isEmpty()) {
        $this->assertTrue($node->get('oe_project_eu_budget')->isEmpty());
      }
      else {
        // Assert the value is in decimal format in the new field.
        $this->assertEquals(123456.00, $node->get('oe_project_eu_budget')->value);
      }
      if ($node->get('oe_project_budget_eu')->isEmpty()) {
        $this->assertTrue($node->get('oe_project_eu_contrib')->isEmpty());
      }
      else {
        // Assert the value is in decimal format in the new field.
        $this->assertEquals(654321.00, $node->get('oe_project_eu_contrib')->value);
      }
    }
  }

}
