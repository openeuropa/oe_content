<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Class RawCorporateContentContext.
 */
class RawCorporateContentContext extends RawDrupalContext {

  /**
   * Keep track of created corporate entities so they can be cleaned up.
   *
   * @var array
   */
  protected $entities = [];

  /**
   * Remove any created corporate entities.
   *
   * @AfterScenario
   */
  public function cleanEntities() {
    foreach ($this->entities as $entity) {
      $this->getDriver()->getCore()->entityDelete($entity->id(), $entity);
    }
    $this->entities = [];
  }

}
