<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content\Traits;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Gather sub-entity context.
 *
 * This trait is to be used on contexts dealing with content types that make use
 * of sub-entities.
 */
trait GatherSubEntityContextTrait {

  /**
   * Sub-entity context.
   *
   * @var \Drupal\Tests\oe_content\Behat\Content\SubEntityContext
   */
  protected $subEntityContext;

  /**
   * Gather sub-entity context contexts.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The before scenario scope.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope): void {
    $this->subEntityContext = $scope->getEnvironment()->getContext('Drupal\Tests\oe_content\Behat\Content\SubEntityContext');
  }

}
