<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines step definitions specifically for testing the RDF entities.
 *
 * This is needed due to issues with the Kernel testing of the RDF entity module
 * in Drupal 8.6.
 */
class RdfEntityContext extends RawDrupalContext {

  /**
   * Before scenario hook for the rdf entities tests.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The Hook scope.
   *
   * @BeforeScenario @rdf-test
   */
  public function beforeScenarioRdfSetup(BeforeScenarioScope $scope): void {
    // In case a URI is being set in the scenario, keep track of the old one.
    $existing = \Drupal::configFactory()->get('oe_content.settings')->get('provenance_uri');
    \Drupal::state()->set('behat_rdf_test_existing_provenance', $existing);
  }

  /**
   * After scenario hook for the rdf entities tests.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The Hook scope.
   *
   * @AfterScenario @rdf-test
   */
  public function afterScenarioRdfCleanUp(AfterScenarioScope $scope): void {
    // If a provenance URI was changed, restore the previous one..
    $provenance_uri = \Drupal::state()->get('behat_rdf_test_existing_provenance');
    \Drupal::configFactory()
      ->getEditable('oe_content.settings')
      ->set('provenance_uri', $provenance_uri)
      ->save();
    \Drupal::state()->delete('behat_rdf_test_existing_provenance');

    $label = \Drupal::state()->get('behat_rdf_test_created_entity');
    if ($label) {
      $entities = \Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['label' => $label]);
      if ($entities) {
        $entity = reset($entities);
        $entity->delete();
      }
      \Drupal::state()->delete('behat_rdf_test_created_entity');
    }
  }

  /**
   * Configures the Provenance URI.
   *
   * @Given /^the site Provenance URI is set to "([^"]*)"/
   */
  public function theSiteProvenanceUriIsSetTo($arg1) {
    $existing = \Drupal::configFactory()->get('oe_content.settings')->get('provenance_uri');
    // Keep track of the existing URI to clear it after the test.
    \Drupal::state()->set('behat_rdf_test_existing_provenance', $existing);
    \Drupal::configFactory()
      ->getEditable('oe_content.settings')
      ->set('provenance_uri', $arg1)
      ->save();

    if (\Drupal::configFactory()->get('oe_content.settings')->get('provenance_uri') !== $arg1) {
      throw new \Exception('The Provenance URI was not set in the configuration.');
    }
  }

  /**
   * Creates a test RDF entity.
   *
   * @Given /^I create an "([^"]*)" RDF entity with the name "([^"]*)"$/
   */
  public function iCreateTestRdfEntityWithTheName($arg1, $arg2) {
    /** @var \Drupal\rdf_entity\RdfEntityTypeInterface[] $types */
    $types = \Drupal::entityTypeManager()->getStorage('rdf_type')->loadMultiple();
    $map = [];
    foreach ($types as $id => $type) {
      $map[$type->label()] = $type->id();
    }

    if (!isset($map[$arg1])) {
      throw new \InvalidArgumentException('The provided entity type is not correct.');
    }

    $entity = \Drupal::entityTypeManager()->getStorage('rdf_entity')->create([
      'rid' => $map[$arg1],
      'label' => $arg2,
    ]);
    $entity->save();

    if (!\Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['rid' => $map[$arg1], 'label' => $arg2])) {
      throw new \Exception('The RDF entity did not get created.');
    }

    // Keep track of the entity to clear it after the test.
    \Drupal::state()->set('behat_rdf_test_created_entity', $arg2);
  }

  /**
   * Tests that the created RDF entity has the correct provenance URI.
   *
   * @Then /^the Provenance URI of the RDF entity with the name "([^"]*)" should be "([^"]*)"$/
   */
  public function theProvenanceUriOfTheRdfEntityWithTheNameShouldBe($arg1, $arg2) {
    $entities = \Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['label' => $arg1]);
    if (!$entities) {
      throw new \Exception('The RDF entity could not be found.');
    }

    /** @var \Drupal\rdf_entity\RdfInterface $rdf */
    $rdf = reset($entities);
    if ($rdf->get('provenance_uri')->value !== $arg2) {
      throw new \Exception('The RDF entity did not get created with the proper Provenance URI.');
    }
  }

  /**
   * Removes a RDF entity with a given name.
   *
   * @Then /^I delete the RDF entity with the name "([^"]*)"$/
   */
  public function iDeleteTheRdfEntityWithTheName($arg1) {
    $entities = \Drupal::entityTypeManager()->getStorage('rdf_entity')->loadByProperties(['label' => $arg1]);
    if ($entities) {
      $entity = reset($entities);
      $entity->delete();
      return;
    }

    throw new \Exception('RDF entity was not found to be deleted.');
  }

}
