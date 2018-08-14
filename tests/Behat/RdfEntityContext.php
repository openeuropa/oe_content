<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\ConfigContext;
use Drupal\rdf_entity\RdfInterface;

/**
 * Defines step definitions specifically for testing the RDF entities.
 *
 * This is needed due to issues with the Kernel testing of the RDF entity module
 * in Drupal 8.6.
 *
 * We are extending ConfigContext to override the setConfig() method until
 * issue https://github.com/jhedstrom/drupalextension/issues/498 is fixed.
 */
class RdfEntityContext extends ConfigContext {

  /**
   * An array of RDF entities we need to keep track to delete.
   *
   * @var \Drupal\rdf_entity\RdfInterface[]
   */
  protected $rdfEntities = [];

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
    $this->setConfig('oe_content.settings', 'provenance_uri', 'http://example.com');
  }

  /**
   * Configures the Provenance URI.
   *
   * @Given the site Provenance URI is set to :uri
   * @And the site Provenance URI is set to :uri
   */
  public function theSiteProvenanceUriIsSetTo($uri): void {
    $this->setConfig('oe_content.settings', 'provenance_uri', $uri);
  }

  /**
   * User creation and login for creating RDF entities.
   *
   * A custom step that creates a user that has the permissions to create and
   * view RDF entities of a give type.
   *
   * @Given I am logged in with a user that can create and view :bundle RDF entities
   */
  public function iAmLoggedInWithUserThatCanCreateAndViewRdfEntityTypes($bundle): void {
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
  public function iCreateTestRdfEntityWithTheName($bundle, $name): void {
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
  public function theProvenanceUriOfTheRdfEntityWithTheNameShouldBe($name, $provenance_uri): void {
    $entity = $this->loadRdfEntityByName($name);
    if ($entity->get('provenance_uri')->value !== $provenance_uri) {
      throw new \Exception('The RDF entity did not get created with the proper Provenance URI.');
    }
  }

  /**
   * Removes a RDF entity with a given name.
   *
   * @Then /^I delete the RDF entity with the name "([^"]*)"$/
   */
  public function iDeleteTheRdfEntityWithTheName($arg1): void {
    $entity = $this->loadRdfEntityByName($arg1);
    $entity->delete();
  }

  /**
   * Asserts that the current user has access to a given RDF entity.
   *
   * @Then I should have :operation access to the RDF entity with the name :name
   */
  public function iShouldHaveAccessToTheRdfEntityWithTheName($operation, $name): void {
    $entity = $this->loadRdfEntityByName($name);
    if (!$entity->access($operation)) {
      throw new \Exception('The current user does not have access for this operation and they should.');
    }
  }

  /**
   * Asserts that the current user does not have access to a given RDF entity.
   *
   * @Then I should not have :operation access to the RDF entity with the name :name
   */
  public function iShouldNotHaveAccessToTheRdfEntityWithTheName($operation, $name): void {
    $entity = $this->loadRdfEntityByName($name);
    var_dump('entiy prov: ' . $entity->get('provenance_uri')->value);
    var_dump('site prov: ' . \Drupal::config('oe_content.settings')->get('provenance_uri'));
    if ($entity->access($operation)) {
      throw new \Exception('The current user has access for this operation and they should not..');
    }
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
      throw new \InvalidArgumentException('No bundle provided..');
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

  /**
   * Loads an RDF entity by name.
   *
   * @param string $name
   *   The name of the entity.
   *
   * @return \Drupal\rdf_entity\RdfInterface
   *   The RDF entity instance.
   */
  protected function loadRdfEntityByName($name): RdfInterface {
    $entities = \Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['label' => $name]);

    if (!$entities) {
      throw new \Exception('RDF entity not found.');
    }

    return reset($entities);
  }

  /**
   * {@inheritdoc}
   *
   * To be removed when https://github.com/jhedstrom/drupalextension/issues/498
   * gets fixed.
   */
  public function setConfig($name, $key, $value) {
    $backup = $this->getDriver()->configGet($name, $key);
    $this->getDriver()->configSet($name, $key, $value);
    if (!array_key_exists($name, $this->config)) {
      $this->config[$name][$key] = $backup;
      return;
    }

    if (!array_key_exists($key, $this->config[$name])) {
      $this->config[$name][$key] = $backup;
    }
  }

}
