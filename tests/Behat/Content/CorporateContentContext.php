<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Tests\oe_content\Behat\Hook\Scope\BeforeParseEntityFieldsScope;
use Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create corporate entities.
 */
class CorporateContentContext extends RawDrupalContext {

  use EntityLoadingTrait;

  /**
   * Create a node.
   *
   * @Given the following :bundle_label content:
   */
  public function createNode(string $bundle_label, TableNode $table): void {
    // Get and alter fields.
    $fields = $table->getRowsHash();
    $bundle = $this->loadEntityByLabel('node_type', $bundle_label)->id();
    $fields['type'] = $bundle;
    $fields = $this->parseFields('node', $bundle, $fields);

    // Create node.
    $this->nodes[] = \Drupal::entityTypeManager()->getStorage('node')->create($fields);
  }

  /**
   * Update a node.
   *
   * @Given the ":bundle_label" content titled ":title" is updated as follow:
   */
  public function updateNode(string $bundle_label, string $title, TableNode $table): void {
    // Get and alter fields.
    $fields = $table->getRowsHash();
    $bundle = $this->loadEntityByLabel('node_type', $bundle_label)->id();
    $fields = $this->parseFields('node', $bundle, $fields);

    // Set field value and save node.
    $node = $this->loadEntityByLabel('node', $title, $bundle);
    foreach ($fields as $name => $value) {
      $node->set($name, $value);
    }
    $node->save();
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
    $fields = $this->dispatchEntityAwareHook($scope);

    // We have to cast as parseEntityFields() expects an object passed by ref.
    $fields = (object) $fields;
    $this->parseEntityFields($entity_type, $fields);

    return (array) $fields;
  }

  /**
   * Dispatch entity aware hooks.
   *
   * @param \Drupal\Tests\oe_content\Behat\Hook\Scope\EntityAwareHookScopeInterface $scope
   *   Hook scope to dispatch.
   *
   * @return array
   *   Merged dispatch results.
   */
  protected function dispatchEntityAwareHook(EntityAwareHookScopeInterface $scope) {
    $return = [];
    /** @var \Behat\Testwork\Call\CallResults $results */
    $call_results = $this->dispatcher->dispatchScopeHooks($scope);

    foreach ($call_results as $result) {
      // The dispatcher suppresses exceptions, throw them here if there are any.
      if ($result->hasException()) {
        $exception = $result->getException();
        throw $exception;
      }
      $return = array_merge($return, $result->getReturn());
    }

    return $return;
  }

}
