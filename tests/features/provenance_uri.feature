@api
# This is needed due to issues with the Kernel testing of the RDF entity module
# in Drupal 8.6.
Feature: Provenance feature
  In order to deal with RDF entities on a site level
  As a content editor
  I should be able create RDF entities with their provenance URIs pre filled

  @rdf-test
  Scenario: Provenance URI is pre-filled when creating an RDF entity
    Given the site Provenance URI is set to "http://example-provenance.com"
    And I create an "Announcement" RDF entity with the name "Test Provenance"
    Then the Provenance URI of the RDF entity with the name "Test Provenance" should be "http://example-provenance.com"

  @rdf-test
  Scenario: Access to manage RDF entities needs to take the provenance URI into account.
    Given I am logged in with a user that can create and view "Announcement" RDF entities
    And the site Provenance URI is set to "http://example-provenance.com"
    And I create an "Announcement" RDF entity with the name "Test Provenance Access One"
    And the site Provenance URI is set to "http://example-provenance-another-site.com"
    And I create an "Announcement" RDF entity with the name "Test Provenance Access Two"
    Then I should not have "edit" access to the RDF entity with the name "Test Provenance Access One"
    And I should not have "delete" access to the RDF entity with the name "Test Provenance Access One"
    And I should have "edit" access to the RDF entity with the name "Test Provenance Access Two"
    And I should have "delete" access to the RDF entity with the name "Test Provenance Access Two"