<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Call\CallResults;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\AfterParseEntityFieldsScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\AfterSaveEntityScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeSaveEntityScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create corporate entities.
 */
class CorporateContentContext extends RawDrupalContext {

  use EntityLoadingTrait;

  /**
   * Keep track of created corporate entities so they can be cleaned up.
   *
   * @var array
   */
  protected $entities = [];

  /**
   * Create an entity.
   *
   * Example:
   *
   * Given the following Event Content entity:
   *   | Title                   | Event demo page          |
   *   | Type                    | exhibitions              |
   *   | Introduction            | Event introduction text  |
   *   | Description summary     | Description summary text |
   *   | Description             | Event description        |
   *   | Start date              | 2019-02-21 10:30:00      |
   *   | End date                | 2019-02-21 18:30:00      |
   *   | Languages               | Valencian                |
   *
   * Use entity type and bundle labels to refer to the entity.
   *
   * Field names and/or values can be transformed by using the following hooks:
   *
   *  - @BeforeParseEntityFields(ENTITY_TYPE, ENTITY_BUNDLE)
   *  - @AfterParseEntityFields(ENTITY_TYPE, ENTITY_BUNDLE)
   *
   * For an example of field transformations refer to:
   *
   * - @see \Drupal\Tests\oe_content\Behat\Content\Node\EventContentContext::alterEventFields()
   * - @see \Drupal\Tests\oe_content\Behat\Content\Venue\DefaultVenueContext::alterVenueFields()
   *
   * This step also fires a @BeforeSaveEntity(ENTITY_TYPE, ENTITY_BUNDLE) right
   * before saving the entity.
   *
   * @param string $bundle_label
   *   Entity bundle label.
   * @param string $entity_type_label
   *   Entity type label.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   List of fields.
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

    // Dispatch after save hook.
    $scope = new AfterSaveEntityScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $entity);
    $this->dispatchEntityAwareHook($scope);

    // Make sure that the created entities are tracked and can be cleaned up.
    $this->entities[] = $entity;

    // Clears the static cache of DatabaseCacheTagsChecksum.
    // Static caches are typically cleared at the end of the request since a
    // typical web request is short lived and the process disappears when the
    // page is delivered. But if a Behat test is using DrupalContext then Drupal
    // will be bootstrapped early on (in the BeforeSuiteScope step). This starts
    // a request which is not short lived, but can live for several minutes
    // while the tests run. During the lifetime of this request there will be
    // steps executed that do requests of their own, changing the state of the
    // Drupal site. This does not however update any of the statically cached
    // data of the parent request, so this is totally unaware of the changes.
    // This causes unexpected behaviour like the failure to invalidate some
    // caches because DatabaseCacheTagsChecksum::invalidateTags() keeps a local
    // storage of which cache tags were invalidated, and this is not reset in
    // time.
    \Drupal::service('cache_tags.invalidator')->resetCheckSums();
  }

  /**
   * Update an existing entity, given its bundle, entity type and title.
   *
   * Example:
   *
   * Given the Event Content "Event demo page" is updated as follows:
   *   | Start date | 2019-02-21 12:30:00 |
   *   | End date   | 2019-02-21 20:30:00 |
   *
   * Use entity type and bundle labels to refer to the entity.
   *
   * Field names and/or values can be transformed by using the following hooks:
   *
   *  - @BeforeParseEntityFields(ENTITY_TYPE, ENTITY_BUNDLE)
   *  - @AfterParseEntityFields(ENTITY_TYPE, ENTITY_BUNDLE)
   *
   * For an example of field transformations refer to:
   *
   * - @see \Drupal\Tests\oe_content\Behat\Content\Node\EventContentContext::alterEventFields()
   * - @see \Drupal\Tests\oe_content\Behat\Content\Venue\DefaultVenueContext::alterVenueFields()
   *
   * This step also fires a @BeforeSaveEntity(ENTITY_TYPE, ENTITY_BUNDLE) right
   * before saving the entity.
   *
   * @param string $bundle_label
   *   Entity bundle label.
   * @param string $entity_type_label
   *   Entity type label.
   * @param string $label
   *   Entity label.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   List of fields.
   *
   * @Given the :bundle_label :entity_type_label :label is updated as follows:
   */
  public function updateEntity(string $bundle_label, string $entity_type_label, string $label, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();
    $fields = $this->parseFields($entity_type, $bundle, $fields);

    // Set field value and save the entity.
    $entity = $this->loadEntityByLabel($entity_type, $label, $bundle);
    foreach ($fields as $name => $value) {
      $entity->set($name, $value);
    }

    // Dispatch before save hook.
    $scope = new BeforeSaveEntityScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $entity);
    $this->dispatchEntityAwareHook($scope);

    $entity->save();

    // Dispatch after save hook.
    $scope = new AfterSaveEntityScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $entity);
    $this->dispatchEntityAwareHook($scope);

    // Clears the static cache of DatabaseCacheTagsChecksum.
    // Static caches are typically cleared at the end of the request since a
    // typical web request is short lived and the process disappears when the
    // page is delivered. But if a Behat test is using DrupalContext then Drupal
    // will be bootstrapped early on (in the BeforeSuiteScope step). This starts
    // a request which is not short lived, but can live for several minutes
    // while the tests run. During the lifetime of this request there will be
    // steps executed that do requests of their own, changing the state of the
    // Drupal site. This does not however update any of the statically cached
    // data of the parent request, so this is totally unaware of the changes.
    // This causes unexpected behaviour like the failure to invalidate some
    // caches because DatabaseCacheTagsChecksum::invalidateTags() keeps a local
    // storage of which cache tags were invalidated, and this is not reset in
    // time.
    \Drupal::service('cache_tags.invalidator')->resetCheckSums();
  }

  /**
   * Remove any created corporate entities.
   *
   * @AfterScenario
   */
  public function cleanEntities(): void {
    foreach ($this->entities as $entity) {
      $this->getDriver()->getCore()->entityDelete($entity->id(), $entity);
    }
    $this->entities = [];
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
  protected function parseFields(string $entity_type, string $bundle, array $fields): array {
    $scope = new BeforeParseEntityFieldsScope($entity_type, $bundle, $this->getDrupal()->getEnvironment(), $fields);
    $this->dispatchEntityAwareHook($scope);

    // We have to cast as parseEntityFields() expects a reference to an object.
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
