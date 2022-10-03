@api
Feature: Call for tenders content creation
  In order to have Calls for tenders on the site
  As an editor
  I need to be able to create and see call for tenders items

  @javascript
  @batch3
  Scenario: Creation of a Call for tenders content through the UI.
    Given I am logged in as a user with the "create oe_call_tenders content, access content, edit own oe_call_tenders content, view published skos concept entities" permission
    And the following document:
      | name          | file       |
      | My Document 1 | sample.pdf |

    When I visit "the Call for tenders creation page"
    And I fill in "Page title" with "My Call for tenders 1"
    And I fill in "Subject" with "EU financing"
    And I fill in "Teaser" with "My Teaser text"
    And I fill in "Introduction" with "My Introduction text"
    And I fill in "Reference" with "My Reference text"
    And I fill in "Publication date" with the date "14-07-2020"
    And I fill in "Opening date" with the date "24-07-2020"
    And I fill in "Deadline date" with the date "31-07-2020"
    And I fill in "Deadline date" with the time "23:45:00"
    And I fill in "Responsible department" with "Audit Board of the European Communities (http://publications.europa.eu/resource/authority/corporate-body/ABEC)"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I fill in "Body text" with "My Body text"
    And I fill in "Use existing media" with "My Document 1" in the "Documents" region
    And I press "Save"
    Then I should see "My Call for tenders 1"
    And I should see "My Teaser text"
    And I should see "My Introduction text"
    And I should see "My Reference text"
    And I should see "07/14/2020"
    And I should see "07/24/2020"
    And I should see "07/31/2020 - 23:45"
    And I should see "Audit Board of the European Communities"
    And I should see "My Body text"
    And I should see "My Document 1"
    And I should not see "Committee on Agriculture and Rural Development"

  @javascript
  @batch3
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_call_tenders content, access content, edit own oe_call_tenders content, view published skos concept entities" permission
    When I visit "the Call for tenders creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    When I fill in "Page title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Nam eleifend ipsum. Text to remove"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I fill in "Subject" with "EU financing"
    And I fill in "Publication date" with the date "14-07-2020"
    And I fill in "Deadline date" with the date "31-07-2020"
    And I fill in "Deadline date" with the time "23:45:00"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development (http://publications.europa.eu/resource/authority/corporate-body/EP_AGRI)"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "Text to remove"
    And I should see "ametas Introduction."
    And I should see "nullamsa Alternative title."
    And I should see "amet Teaser."
