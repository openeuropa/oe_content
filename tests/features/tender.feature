@api
Feature: Call for Tender content creation
  In order to have Calls for tender on the site
  As an editor
  I need to be able to create and see call for tender items

  @javascript
  Scenario: Creation of a Call for Tender content through the UI.
    Given I am logged in as a user with the "create oe_tender content, access content, edit own oe_tender content, view published skos concept entities" permission
    And the following document:
      | name          | file        |
      | My Document 1 | sample.pdf  |

    When I visit "the Call for tender creation page"
    And I fill in "Title" with "My Call for Tender 1"
    And I fill in "Subject" with "EU financing"
    And I fill in "Teaser" with "My Teaser text"
    And I fill in "Introduction" with "My Introduction text"
    And I fill in "Reference" with "My Reference text"
    And I set "Publication date" to the date "14-07-2020"
    And I set "Opening" to the date "24-07-2020"
    And I set "Deadline date" to the date "31-07-2020 23:45" using format "d-m-Y G:i"
    And I fill in "Responsible department" with "Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I fill in "Body text" with "My Body text"
    And I fill in "Use existing media" with "My Document 1" in the "Documents" region
    And I press "Save"
    Then I should see "My Call for Tender 1"
    And I should not see "My Teaser text"
    And I should not see "My Introduction text"
    And I should see "My Reference text"
    And I should see "07/14/2020"
    And I should see "07/24/2020"
    And I should see "07/31/2020 - 23:45"
    And I should see "Audit Board of the European Communities"
    And I should see "My Body text"
    And I should see "My Document 1"
    And I should not see "Committee on Agriculture and Rural Development"
