<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\oe_content_sub_entity\Entity\SubEntityInterface;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create corporate entities.
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
   * Update an existing sub entity, given its bundle, entity type and title.
   *
   * Example:
   *
   * Given the sub entity Document "Document reference" "Document reference to
   * My Document" is updated as follows:
   *   | Published | No |
   *
   * Use entity type and bundle labels to refer to the entity.
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
   * @see \Drupal\Tests\oe_content\Behat\Content\RawEntityContext::saveEntity()
   *
   * @Given the sub entity :bundle_label :entity_type_label :name is updated as follows:
   */
  public function updateSubEntity(string $bundle_label, string $entity_type_label, string $label, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    if (!isset($fields['Name'])) {
      $fields['Name'] = $label;
    }
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();

    // Set field value and save the entity.
    $entity = $this->loadSubEntityByName($entity_type, $label, $bundle);

    // Update entity.
    $this->updateEntity($entity, $fields);
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
   * Load a sub entity by type, label and, optionally, by bundle.
   *
   * @param string $entity_type
   *   Entity type ID.
   * @param string $label
   *   Entity label.
   * @param string $bundle
   *   Entity bundle ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Entity object, if any.
   */
  protected function loadSubEntityByName(string $entity_type, string $label, string $bundle): EntityInterface {
    $exception_message = "No '$entity_type' entity of type '$bundle' with label '$label' has been found.";
    $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    // Find entity in the content storage.
    $entity = ContentStorage::getInstance()->getEntity($label);
    if (!$entity) {
      throw new \InvalidArgumentException($exception_message);
    }
    $properties = [
      'id' => $entity->id(),
    ];

    // If bundle is set then add it to the query properties.
    if ($bundle) {
      $bundle_key = $storage->getEntityType()->getKey('bundle');
      $properties[$bundle_key] = $bundle;
    }
    // Load the sub entity from the database.
    $entities = $storage->loadByProperties($properties);

    if (empty($entities)) {
      throw new \InvalidArgumentException($exception_message);
    }

    return reset($entities);
  }

  /**
   * Assert the sub entity exists.
   *
   * Example:
   *
   * Then the sub entity Document "Document reference" entity with name
   * "Document reference to My Document" exists
   *
   * @param string $bundle_label
   *   Entity bundle label.
   * @param string $entity_type_label
   *   Entity type label.
   * @param string $name
   *   Fake entity label.
   *
   * @Then the sub entity :bundle_label :entity_type_label entity with name :name exists
   */
  public function subEntityExists(string $bundle_label, string $entity_type_label, string $name): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();
    $this->loadSubEntityByName($entity_type, $name, $bundle);
  }

}
