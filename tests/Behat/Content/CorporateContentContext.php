<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Tests\oe_content\Traits\EntityLoadingTrait;

/**
 * Context to create corporate entities.
 */
class CorporateContentContext extends RawEntityContext {

  use EntityLoadingTrait;

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
   * @param string $bundle_label
   *   Entity bundle label.
   * @param string $entity_type_label
   *   Entity type label.
   * @param \Behat\Gherkin\Node\TableNode $table
   *   List of fields.
   *
   * @see \Drupal\Tests\oe_content\Behat\Content\RawEntityContext::saveEntity()
   *
   * @Given the following :bundle_label :entity_type_label entity:
   */
  public function createEntity(string $bundle_label, string $entity_type_label, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    $fields[$definition->getKey('bundle')] = $bundle;
    $fields = $this->parseFields($entity_type, $bundle, $fields);

    // Create and save entity.
    $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->create($fields);
    $this->saveEntity($entity_type, $bundle, $entity);
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
   * @Given the :bundle_label :entity_type_label :label is updated as follows:
   */
  public function updateEntity(string $bundle_label, string $entity_type_label, string $label, TableNode $table): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();

    // Get and alter fields.
    $fields = $table->getRowsHash();
    $fields = $this->parseFields($entity_type, $bundle, $fields);

    // Set field value and save the entity.
    $entity = $this->loadEntityByLabel($entity_type, $label, $bundle);
    foreach ($fields as $name => $value) {
      $entity->set($name, $value);
    }

    // Update entity.
    $this->saveEntity($entity_type, $bundle, $entity);
  }

  /**
   * Assert the entity exists.
   *
   * Example:
   *
   * Then the News Content entity with title "Test news" exists
   *
   * @param string $bundle_label
   *   Entity bundle label.
   * @param string $entity_type_label
   *   Entity type label.
   * @param string $title
   *   Entity title.
   *
   * @Then the :bundle_label :entity_type_label entity with title :title exists
   */
  public function entityExists(string $bundle_label, string $entity_type_label, string $title): void {
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $entity_type = $definition->id();
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();
    $this->loadEntityByLabel($entity_type, $title, $bundle);
  }

  /**
   * Update an existing sub entity, given its bundle, entity type and title.
   *
   * Example:
   *
   * Given the sub entity Document "Document reference" "Document reference to
   * My Document" is updated as follows:
   * | Published | No |
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
    $fields = $this->parseFields($entity_type, $bundle, $fields);

    // Set field value and save the entity.
    $entity = $this->loadSubEntityByName($entity_type, $label, $bundle);
    foreach ($fields as $name => $value) {
      $entity->set($name, $value);
    }

    // Update entity.
    $this->saveEntity($entity_type, $bundle, $entity);
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
