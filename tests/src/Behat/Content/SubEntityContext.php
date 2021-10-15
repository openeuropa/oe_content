<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create sub-entities.
 *
 * Use this context should to retrieve sub-entities from other contexts too.
 *
 * @see \Drupal\Tests\oe_content\Behat\Content\Traits\GatherSubEntityContextTrait
 */
class SubEntityContext extends RawEntityContext {

  use EntityLoadingTrait;

  /**
   * Keep a list of sub-entities, keyed by their creation name.
   *
   * @var \Drupal\oe_content_sub_entity\Entity\SubEntityInterface[]
   */
  protected $subEntities = [];

  /**
   * Create a sub-entity by assigning an "Behat name" to it.
   *
   * Assigning a name to the sub-entity is necessary to use it when referencing
   * it in nodes or when updating it. with the related step below.
   *
   * Example:
   *
   * Given the following "Document reference" "Document" sub-entity:
   *   | Name     | My doc      |
   *   | Document | example.doc |
   *
   * Use entity type and bundle labels to refer to the entity.
   *
   * @param string $bundle_label
   *   Entity bundle label.
   * @param string $entity_type_label
   *   Entity type label.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   List of fields.
   *
   * @see \Drupal\Tests\oe_content\Behat\Content\RawEntityContext::saveEntity()
   *
   * @Given the following :bundle_label :entity_type_label sub-entity:
   */
  public function createSubEntity(string $bundle_label, string $entity_type_label, TableNode $table): void {
    $fields = $table->getRowsHash();
    if (!isset($fields['Name'])) {
      throw new \InvalidArgumentException('You must specify a "Name" when creating a sub-entity.');
    }

    // Lead entity type definition by its label.
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();

    // Create and save entity.
    $entity = $this->createEntity($definition->id(), $bundle, $table->getRowsHash() + [
      $definition->getKey('bundle') => $bundle,
    ]);

    $this->setSubEntityByName($fields['Name'], $entity);
  }

  /**
   * Update an existing sub-entity, given its entity type and "Behat name".
   *
   * Example:
   *
   * Given the "Document reference" sub-entity "My doc" is updated as follows:
   *   | Published | No |
   *
   * Note: the sub-entity must be created via the related creation step, before
   * being updated. This is necessary since sub-entities do not have labels.
   *
   * @param string $entity_type_label
   *   Entity type label.
   * @param string $name
   *   Entity label.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   List of fields.
   *
   * @see \Drupal\Tests\oe_content\Behat\Content\RawEntityContext::saveEntity()
   *
   * @Given the :entity_type_label sub-entity :name is updated as follows:
   */
  public function updateSubEntity(string $entity_type_label, string $name, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    $entity = $this->getSubEntityByName($name);
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity->id());

    // Update entity.
    $this->updateEntity($entity, $fields);
  }

  /**
   * Assert that a sub-entity does exist with the given type and title.
   *
   * Example:
   *
   * Then the "Document reference" sub-entity "My doc" exists
   *
   * @param string $entity_type_label
   *   Entity type label.
   * @param string $name
   *   Fake entity label.
   *
   * @Then the :entity_type_label sub-entity with title :name exists
   */
  public function subEntityExists(string $entity_type_label, string $name): void {
    $entity_type = $this->loadDefinitionByLabel($entity_type_label)->id();
    $entity = $this->getSubEntityByName($name);
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity->id());
    if ($entity === NULL) {
      throw new \Exception("The sub-entity with name '{$name}' does not exists.");
    }
  }

  /**
   * Reset sub-entities storage once the scenario is over.
   *
   * @AfterScenario
   */
  public function cleanSubEntities(): void {
    $this->subEntities = [];
  }

  /**
   * Store a sub-entity with a given name in the local storage.
   *
   * @param string $name
   *   Sub-entity name.
   * @param \Drupal\oe_content_sub_entity\Entity\SubEntityInterface $entity
   *   Sub-entity object.
   */
  public function setSubEntityByName(string $name, SubEntityInterface $entity) {
    $this->subEntities[$name] = $entity;
  }

  /**
   * Get a sub-entity object from the local storage, if any.
   *
   * @param string $name
   *   Sub-entity name.
   *
   * @return \Drupal\oe_content_sub_entity\Entity\SubEntityInterface
   *   Sub-entity name.
   */
  public function getSubEntityByName(string $name): SubEntityInterface {
    if (!isset($this->subEntities[$name])) {
      throw new \InvalidArgumentException("Sub-entity with name '{$name}' not found.");
    }

    return $this->subEntities[$name];
  }

  /**
   * Get a sub-entity objects from the local storage by names, if any.
   *
   * @param string $names
   *   Sub-entity names delimited by comma.
   *
   * @return \Drupal\oe_content_sub_entity\Entity\SubEntityInterface[]
   *   The list of Sub-entities.
   */
  public function getSubEntityMultipleByNames(string $names): array {
    $entities = [];
    $labels = explode(', ', $names);
    foreach ($labels as $label) {
      $entities[] = $this->getSubEntityByName($label);
    }
    return $entities;
  }

}
