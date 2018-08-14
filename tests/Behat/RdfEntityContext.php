<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines step definitions specifically for testing the RDF entities.
 */
class RdfEntityContext extends RawDrupalContext {

  /**
   * An array of RDF entities we need to keep track to delete.
   *
   * @var \Drupal\rdf_entity\RdfInterface[]
   */
  protected $rdfEntities = [];

  /**
   * The Configuration context.
   *
   * @var \Drupal\DrupalExtension\Context\ConfigContext
   */
  protected $configContext;

  /**
   * Before scenario hook get the ConfigContext.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The Hook scope.
   *
   * @BeforeScenario
   */
  public function gatherConfigContext(BeforeScenarioScope $scope): void {
    $environment = $scope->getEnvironment();
    $this->configContext = $environment->getContext('Drupal\DrupalExtension\Context\ConfigContext');
  }

  /**
   * After scenario hook to delete the RDF entities.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The Hook scope.
   *
   * @AfterScenario @rdf-test
   */
  public function afterScenarioRdfCleanUp(AfterScenarioScope $scope): void {
    if (empty($this->rdfEntities)) {
      return;
    }

    foreach ($this->rdfEntities as $rdf) {
      $rdf->delete();
    }
  }

  /**
   * Before scenario hook sets a default Provenance URI on the site.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The Hook scope.
   *
   * @BeforeScenario @rdf-provenance
   */
  public function setDefaultProvenance(BeforeScenarioScope $scope): void {
    $this->configContext->setConfig('oe_content.settings', 'provenance_uri', 'http://example.com');
  }

  /**
   * Configures the Provenance URI.
   *
   * @Given the site Provenance URI is set to :uri
   */
  public function setSiteProvenanceUri($uri): void {
    $this->configContext->setConfig('oe_content.settings', 'provenance_uri', $uri);
  }

  /**
   * User creation and login for creating RDF entities.
   *
   * A custom step that creates a user that has the permissions to create and
   * view RDF entities of a give type.
   *
   * @Given I am logged in with a user that can create and view :bundle RDF entities
   */
  public function assertLoggedInWithRdfEntityTypePermissions($bundle) {
    /** @var \Drupal\rdf_entity\RdfEntityTypeInterface[] $types */
    $types = \Drupal::entityTypeManager()->getStorage('rdf_type')->loadMultiple();
    $permission_map = [];
    foreach ($types as $id => $type) {
      $permission_map[$type->label()] = "create $id rdf entity";
    }

    if (!isset($permission_map[$bundle])) {
      throw new \InvalidArgumentException('The provided entity type is not correct.');
    }

    $permission = $permission_map[$bundle];
    $permissions = ['view rdf entity', 'view rdf entity overview', $permission];
    $role = $this->getDriver()->roleCreate($permissions);

    // Create user.
    $user = (object) [
      'name' => $this->getRandom()->name(8),
      'pass' => $this->getRandom()->name(16),
      'role' => $role,
    ];
    $user->mail = "{$user->name}@example.com";
    $this->userCreate($user);

    // Assign the temporary role with given permissions.
    $this->getDriver()->userAddRole($user, $role);
    $this->roles[] = $role;

    // Login.
    $this->login($user);
  }

  /**
   * Creates a test RDF entity.
   *
   * @Given I create an :bundle RDF entity with the name :name
   */
  public function createRdfEntityWithLabel($bundle, $name): void {
    $values = [
      'bundle' => $bundle,
      'label' => $name,
    ];

    $this->createRdfEntity($values);
  }

  /**
   * Tests that the created RDF entity has the correct provenance URI.
   *
   * @Then the Provenance URI of the RDF entity with the name :name should be :provenance_uri
   */
  public function assertRdfEntityProvenanceUri($name, $provenance_uri): void {
    $entities = \Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['label' => $name]);
    if (!$entities) {
      throw new \Exception('The RDF entity could not be found.');
    }

    /** @var \Drupal\rdf_entity\RdfInterface $rdf */
    $rdf = reset($entities);
    if ($rdf->get('provenance_uri')->value !== $provenance_uri) {
      throw new \Exception('The RDF entity did not get created with the proper Provenance URI.');
    }
  }

  /**
   * Removes a RDF entity with a given name.
   *
   * @Then I delete the RDF entity with the name :name
   */
  public function deleteRdfEntityByLabel($name): void {
    $entities = \Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['label' => $name]);
    if ($entities) {
      $entity = reset($entities);
      $entity->delete();
      return;
    }

    throw new \Exception('RDF entity was not found to be deleted.');
  }

  /**
   * Creates keeps track of an RDF entity.
   *
   * @param array $values
   *   Values for the RDF entity.
   */
  protected function createRdfEntity(array $values): void {
    $bundle = isset($values['bundle']) ? $values['bundle'] : NULL;
    if (!$bundle) {
      throw new \InvalidArgumentException('No bundle provided.');
    }

    /** @var \Drupal\rdf_entity\RdfEntityTypeInterface[] $types */
    $types = \Drupal::entityTypeManager()->getStorage('rdf_type')->loadMultiple();
    $map = [];
    foreach ($types as $id => $type) {
      $map[$type->label()] = $type->id();
    }

    if (!isset($map[$bundle])) {
      throw new \InvalidArgumentException('The provided entity type is not correct.');
    }

    $values += [
      'rid' => $map[$bundle],
    ];
    $entity = \Drupal::entityTypeManager()->getStorage('rdf_entity')->create($values);
    $entity->save();
    $this->rdfEntities[] = $entity;
  }

}
