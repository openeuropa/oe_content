<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_content\Behat\Content;

use Behat\Gherkin\Node\TableNode;
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
  public function createCorporateEntity(string $bundle_label, string $entity_type_label, TableNode $table): void {
    // Lead entity type definition by its label.
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();

    // Create and save entity.
    $this->createEntity($definition->id(), $bundle, $table->getRowsHash() + [
      $definition->getKey('bundle') => $bundle,
    ]);
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
  public function updateCorporateEntity(string $bundle_label, string $entity_type_label, string $label, TableNode $table): void {
    // Lead entity type definition by its label.
    $definition = $this->loadDefinitionByLabel($entity_type_label);
    $bundle = $this->loadEntityByLabel($definition->getBundleEntityType(), $bundle_label)->id();

    // Get fields and entity.
    $fields = $table->getRowsHash();
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $this->loadEntityByLabel($definition->id(), $label, $bundle);

    // Update entity.
    $this->updateEntity($entity, $fields);
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

}
