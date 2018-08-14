@api
Feature: Provenance feature
  In order to deal with RDF entities on a site level
  As a content editor
  I should be able create RDF entities with their provenance URIs pre filled

  @rdf-test
  Scenario: Provenance URI is pre-filled when creating an RDF entity
    Given the site Provenance URI is set to "http://example-provenance.com"
    And I create an "Announcement" RDF entity with the name "Test Provenance"
    Then the Provenance URI of the RDF entity with the name "Test Provenance" should be "http://example-provenance.com"