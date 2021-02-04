<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Drupal\Tests\oe_content\Behat\Hook\Scope\AfterSaveEntityScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;

/**
 * Trait to collect sub entities to content storage.
 *
 * Sub entities don't have labels, so we can't load them by label. Instead of it
 * they will be collected in ContentStorage where we can take them from.
 */
trait CollectSubEntityTrait {

  /**
   * Name of the entity that is about to be saved.
   *
   * @var string
   */
  protected $subEntityName;

  /**
   * Run before sub entity fields are parsed.
   *
   * We use this to collect sub entities that are created elsewhere.
   * We have to add a before parse hook entry for each supported sub entity.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope $scope
   *   Behat hook scope.
   *
   * @see \Drupal\Tests\oe_content\Behat\Content\CorporateContentContext::parseFields()
   */
  public function collectSubEntityName(BeforeParseEntityFieldsScope $scope): void {
    $fields = $scope->getFields();
    if (!isset($fields['Name'])) {
      throw new \InvalidArgumentException("A sub entity must have a unique, not empty 'Name' field, so that we can fish it back later on.");
    }

    // Store name field.
    // We will use this to store the sub entity object in the after save hook.
    $this->subEntityName = $fields['Name'];
    $scope->removeField('Name');
  }

  /**
   * Store sub entities using the name we have just collected.
   *
   * We use this to collect sub entities that are created by
   * CorporateContentContext::createEntity().
   * We have to add it for each supported sub entity.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\AfterSaveEntityScope $scope
   *   Behat hook scope.
   *
   * @see \Drupal\Tests\oe_content\Behat\Content\CorporateContentContext::createEntity()
   */
  public function storeSubEntityObject(AfterSaveEntityScope $scope): void {
    $content_storage = ContentStorage::getInstance();
    $content_storage->addEntity($this->subEntityName, $scope->getEntity());
    $this->subEntityName = NULL;
  }

}
