<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallResults;
use Drupal\oe_content_entity\Entity\EntityBaseInterface;
use Drupal\Tests\oe_content\Behat\Hook\Scope\AfterParseEntityFieldsScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeSaveEntityScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create corporate entities.
 */
class CorporateContentContext extends RawCorporateContentContext {

  use EntityLoadingTrait;

  /**
   * Create an entity.
   *
   * @Given the following :bundle_label :entity_type_label entity:
   */
  public function createEntity(string $bundle_label, string $entity_type_label, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();
    $fields[$definition->getKey('bundle')] = $bundle;
    $fields = $this->parseFields($entity_type, $bundle, $fields);

    // Create entity.
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->create($fields);

    // Dispatch before save hook.
    $scope = new BeforeSaveEntityScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $entity);
    $this->dispatchEntityAwareHook($scope);

    $entity->save();

    // Make sure that the created entities are tracked and can be cleaned up.
    if ($entity instanceof EntityBaseInterface) {
      $this->entities[] = $entity;
    }
    else {
      $this->nodes[] = $entity;
    }
  }

  /**
   * Update an entity.
   *
   * @Given the :bundle_label :entity_type_label :title is updated as follows:
   */
  public function updateEntity(string $bundle_label, string $entity_type_label, string $title, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();
    $fields = $this->parseFields($entity_type, $bundle, $fields);

    // Set field value and save the entity.
    $entity = $this->loadEntityByLabel($entity_type, $title, $bundle);
    foreach ($fields as $name => $value) {
      $entity->set($name, $value);
    }

    // Dispatch before save hook.
    $scope = new BeforeSaveEntityScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $entity);
    $this->dispatchEntityAwareHook($scope);

    $entity->save();
  }

  /**
   * Parse entity fields.
   *
   * Also fires the following two Behat hooks:
   *
   * - BeforeParseEntityFieldsScope(entity_type,bundle)
   * - AfterParseEntityFields(entity_type,bundle)
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $bundle
   *   Entity bundle.
   * @param array $fields
   *   Fields to be altered.
   *
   * @return array
   *   Parsed fields.
   */
  protected function parseFields(string $entity_type, string $bundle, array $fields) {
    $scope = new BeforeParseEntityFieldsScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $fields);
    $this->dispatchEntityAwareHook($scope);

    // We have to cast as parseEntityFields() expects an object passed by ref.
    $fields = (object) $scope->getFields();
    $this->parseEntityFields($entity_type, $fields);

    // We cast it back so we keep dealing with arrays.
    $fields = (array) $fields;
    $scope = new AfterParseEntityFieldsScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $fields);
    $this->dispatchEntityAwareHook($scope);

    return $scope->getFields();
  }

  /**
   * Dispatch entity aware hooks.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface $scope
   *   Hook scope to dispatch.
   *
   * @return \Behat\Testwork\Call\CallResults
   *   Results of hook dispatch.
   */
  protected function dispatchEntityAwareHook(EntityAwareHookScopeInterface $scope): CallResults {
    $results = $this->dispatcher->dispatchScopeHooks($scope);

    foreach ($results as $result) {
      // The dispatcher suppresses exceptions, throw them here if there are any.
      if ($result->hasException()) {
        $exception = $result->getException();
        throw $exception;
      }
    }

    return $results;
  }

}
