<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_content\Behat;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Defines step definitions that are generally useful in this project.
 */
class RdfEntityContext extends RawDrupalContext {

  /**
   * Enable the RDF entity test module.
   *
   * @param \Behat\Behat\Hook\Scope\BeforeScenarioScope $scope
   *   The Hook scope.
   *
   * @BeforeScenario @rdf-test
   */
  public function setupSelectionPage(BeforeScenarioScope $scope): void {
    \Drupal::service('module_installer')->install(['rdf_entity_test']);

    // Keep track of the existing URI to clear it after the test.
    $existing = \Drupal::configFactory()->get('oe_content.settings')->get('provenance_uri');
    \Drupal::state()->set('behat_rdf_test_existing_provenance', $existing);
  }

  /**
   * Disable the RDF entity test module.
   *
   * @param \Behat\Behat\Hook\Scope\AfterScenarioScope $scope
   *   The Hook scope.
   *
   * @AfterScenario @rdf-test
   */
  public function revertSelectionPage(AfterScenarioScope $scope): void {
    \Drupal::service('module_installer')->uninstall(['rdf_entity_test']);

    // Restore the defaults.
    $provenance_uri = \Drupal::state()->get('behat_rdf_test_existing_provenance');
    \Drupal::configFactory()
      ->getEditable('oe_content.settings')
      ->set('provenance_uri', $provenance_uri)
      ->save();

    $label = \Drupal::state()->get('behat_rdf_test_created_entity');
    if ($label) {
      $entities = \Drupal::entityTypeManager()->getStorage('rdf')->loadByProperties(['label' => $label]);
      if ($entities) {
        $entity = reset($entities);
        $entity->delete();
      }
    }

    \Drupal::state()->delete('behat_rdf_test_existing_provenance');
    \Drupal::state()->delete('behat_rdf_test_created_entity');
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
   * @Given /^I create a test RDF entity with the name "([^"]*)"$/
   */
  public function iCreateTestRdfEntityWithTheName($arg1) {
    \Drupal::entityTypeManager()->getStorage('rdf')->create([
      'type' => 'dummy',
      'label' => $arg1,
    ])->save();

    if (\Drupal::entityTypeManager()->getStorage('rdf')->loadByProperties(['label' => $arg1])) {
      throw new \Exception('The RDF entity did not get created.');
    }

    // Keep track of the entity to clear it after the test.
    \Drupal::state()->set('behat_rdf_test_created_entity', $arg1);
  }

  /**
   * Tests that the created RDF entity has the correct provenance URI.
   *
   * @Then /^the Provenance URI of the RDF entity with the name "([^"]*)" should be "([^"]*)"$/
   */
  public function theProvenanceUriOfTheRdfEntityWithTheNameShouldBe($arg1, $arg2) {
    $entities = \Drupal::entityTypeManager()->getStorage('rdf')->loadByProperties(['label' => $arg1]);
    if (!$entities) {
      throw new \Exception('The RDF entity could not be found.');
    }

    /** @var \Drupal\rdf_entity\RdfInterface $rdf */
    $rdf = reset($entities);
    if (!$rdf->get('provenance_uri')->value !== $arg2) {
      throw new \Exception('The RDF entity did not get created with the proper Provenance URI.');
    }
  }

}
