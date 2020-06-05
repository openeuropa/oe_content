<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content\Node;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Context to create project content entities.
 */
class ProjectContentContext extends RawDrupalContext {

  /**
   * Creates stakeholder/s for organisation entity.
   *
   * Usage example:
   *
   * Given the following stakeholders:
   *   | name   |
   *   | name 2 |
   *   |   ...  |
   *
   * @Given the following stakeholder(s):
   */
  public function givenStakeholder(TableNode $table):void {
    $stakeholders = $table->getColumnsHash();
    $storage = \Drupal::service('entity_type.manager')->getStorage('oe_organisation');

    foreach ($stakeholders as $stakeholder) {
      $entity = $storage->create([
        'bundle' => 'oe_stakeholder',
        'name' => $stakeholder['name'],
        'status' => 1,
      ]);
      $entity->save();
    }
  }

}
