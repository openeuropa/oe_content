@api
Feature: Consultation content creation
  In order to have Consultation on the site
  As an editor
  I need to be able to create and see Consultation items

  @javascript
  Scenario: Creation of a Consultation content through the UI.
    Given I am logged in as a user with the "create oe_consultation content, access content, edit own oe_consultation content, view published skos concept entities, manage corporate content entities" permission
    And the following documents:
      | name          | file         |
      | My Document 1 | sample.pdf   |
      | My Document 2 | document.pdf |
    And the following "Publication" Content entity:
      | Title | Publication node |

    When I visit "the Consultation creation page"
    And I fill in "Page title" with "Consultation title"
    And I fill in "Introduction" with "Introduction text"
    And I set "Opening" to the date "14-01-2021"
    And I set "Deadline" to the date "31-01-2021 00:00" using format "d-m-Y H:i"
    And I fill in "Departments" with "Associated African States and Madagascar"
    And I fill in "Target audience" with "Target audience text"
    And I fill in "Why we are consulting" with "Why we are consulting text"
    And I fill in "Respond to the consultation" with "Respond to the consultation text"
    And I fill in "Respond to the consultation (closed status text)" with "Respond to the consultation (closed status text) text"
    And I fill in "URL" with "http://respond.com"
    And I fill in "Link text" with "Respond to the questionnaire"
    And I fill in "Consultation outcome" with "Consultation outcome text"
    And I fill in "Use existing media" with "My Document 1"
    And I fill in "Additional information" with "Additional information text"
    And I fill in "Legal notice" with "Legal notice text"

    # Create General contact.
    And I press "Add new Contact"
    And I wait for AJAX to finish
    And I fill in "Name" with "General contact"
    And I press "Create Contact"

    # Create document reference to Document media.
    And I press "Add new document reference"
    And I wait for AJAX to finish
    And I fill in "Use existing media" with "My Document 2" in the "Consultation documents" region
    And I press "Create document reference"
    And I wait for AJAX to finish

    # Create document reference to Publication node.
    And I select "Publication" from "oe_consultation_documents[actions][bundle]"
    And I press "Add new document reference"
    And I wait for AJAX to finish
    And I fill in "Publication" with "Publication node" in the "Consultation documents" region
    And I press "Create document reference"
    And I wait for AJAX to finish

    And I fill in "Teaser" with "Teaser text"
    And I fill in "Content owner" with "Audit Board of the European Communities"
    And I fill in "Subject" with "export financing"
    And I press "Save"
    Then I should see the text "Consultation title"
    And I should see the text "Introduction text"
    And I should see the text "01/14/2021"
    And I should see the text "01/31/2021 - 00:00"
    And I should see the text "Associated African States and Madagascar"
    And I should see the text "Target audience text"
    And I should see the text "Why we are consulting text"
    And I should see the text "Respond to the consultation text"
    And I should see the text "Respond to the consultation (closed status text)"
    And I should see the link "Respond to the questionnaire" pointing to "http://respond.com"
    And I should see the text "Consultation outcome text"
    And I should see the text "Additional information text"
    And I should see the text "Legal notice text"
    And I should see "sample.pdf"
    And I should see the text "General contact"
    # Document from document reference is shown.
    And I should see "document.pdf"
    # Publication from document reference is shown.
    And I should see "Publication node"


  @javascript
  Scenario: Test the maximum string length and the valid date requirements of the Consultation content type.
    Given I am logged in as a user with the "create oe_consultation content, access content, edit own oe_consultation content, view published skos concept entities, manage corporate content entities" permission
    When I visit "the Consultation creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I should see the text "Content limited to 150 characters, remaining: 150" in the "teaser form element"
    When I fill in "Page title" with "Consultation title scelerisque eros mi, eget tempus nibh finibus sed. Praesent id ex bibendum, luctus nisl ut, suscipit lectus. Nullam vitae neque mi. Aliquam eleifend d Text to remove."
    And I set "Opening" to the date "14-01-2021"
    And I set "Deadline" to the date "31-01-2021 00:00" using format "d-m-Y H:i"
    And I fill in "Target audience" with "Target audience text"
    And I fill in "Introduction" with "Nulla consectetur eleifend mi id pretium. Donec dapibus, nunc vel ullamcorper condimentum, ipsum massa vehicula mauris, a iaculis massa magna pharetra ipsum. Sed laoreet augue bibendum nulla sagittis, nec tempus est viverra. Fusce tempus massa trist Text to remove."
    And I fill in "Alternative title" with "Phasellus scelerisque eros mi, eget tempus nibh finibus sed. Praesent id ex bibendum, luctus nisl ut, suscipit lectus. Nullam vitae neque mi. Aliquam eleifend dolor puru Text to remove."
    And I fill in "Teaser" with "Ut sollicitudin lectus in turpis scelerisque elementum. Pellentesque ullamcorper ullamcorper erat, volutpat vehicula sem facilisis sed. Nunc vestibul Text to remove."
    And I fill in "Content owner" with "Audit Board of the European Communities"
    And I fill in "Subject" with "export financing"
    And I press "Save"
    Then I should not see the text "The text to remove."
