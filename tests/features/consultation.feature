@api
Feature: Consultation content creation
  In order to have Consultation on the site
  As an editor
  I need to be able to create and see Consultation items

  @javascript
  @batch1
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
    And I fill in "Opening" with the date "14-01-2021"
    And I fill in "Deadline" with the date "31-01-2021"
    And I fill in "Deadline" with the time "00:00:00"
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
    Then I should see the text "My document 2"

    # Create document reference to Publication node.
    When I select "Publication" in the "Consultation documents" region
    And I press "Add new document reference"
    And I wait for AJAX to finish
    And I fill in "Publication" with "Publication node" in the "Consultation documents" region
    And I press "Create document reference"
    And I wait for AJAX to finish
    Then I should see the text "Publication node"

    When I fill in "Teaser" with "Teaser text"
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
  @batch2
  Scenario: Test the maximum string length and the valid date requirements of the Consultation content type.
    Given I am logged in as a user with the "create oe_consultation content, access content, edit own oe_consultation content, view published skos concept entities, manage corporate content entities" permission
    When I visit "the Consultation creation page"
    Then I should see the text "Content limited to 170 characters, remaining: 170" in the "title form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    When I fill in "Page title" with "Consultation title scelerisque eros mi, eget tempus nibh finibus sed. Praesent id ex bibendum, luctus nisl ut, suscipit lectus. Nullam vitae neque mi. Aliquam eleifend d Text to remove."
    And I fill in "Opening" with the date "14-07-2020"
    And I fill in "Deadline" with the date "31-01-2021"
    And I fill in "Deadline" with the time "00:00:00"
    And I fill in "Target audience" with "Target audience text"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I fill in "Content owner" with "Audit Board of the European Communities"
    And I fill in "Subject" with "export financing"
    And I press "Save"
    Then I should not see "Text to remove"
    And I should see "ametas Introduction."
    And I should see "nullamsa Alternative title."
    And I should see "amet Teaser."

  @javascript
  @batch3
  Scenario: Test visibility of document references and ensure that document reference and contact is not deleted after removing from the node.
    Given I am an anonymous user
    And the following General Contact entity:
      | Name | A general contact |
    And the following document:
      | name          | file       |
      | My Document 3 | sample.pdf |
    And the following Document "Document reference" sub-entity:
      | Name     | Document reference to My Document 3 |
      | Document | My Document 3                       |
    And the following Consultation Content entity:
      | Title             | Consultation demo page              |
      | Summary           | Consultation summary                |
      | Teaser            | Consultation teaser                 |
      | Contacts          | A general contact                   |
      | Opening date      | 2019-02-22                          |
      | Deadline          | 2019-03-21 18:30:00                 |
      | Target audience   | Target audience text                |
      | Documents         | Document reference to My Document 3 |
    When I am visiting the "Consultation demo page" content
    Then I should see "sample.pdf"

    When the "Document reference" sub-entity "Document reference to My Document 3" is updated as follows:
      | Published | No |
    And I am visiting the "Consultation demo page" content
    Then I should not see "sample.pdf"

    When I am logged in as a user with the "view unpublished sub entities" permission
    And I am visiting the "Consultation demo page" content
    Then I should see "sample.pdf"

    When I am logged in as a user with the "create oe_consultation content, access content, edit any oe_consultation content, view published skos concept entities, manage corporate content entities, view unpublished sub entities" permission
    And I am visiting the "Consultation demo page" content
    And I click "Edit"
    And I press "Remove" in the "Consultation contacts" region
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove" in the "Consultation contacts" region
    And I wait for AJAX to finish
    And I press "Remove" in the "Consultation documents" region
    Then I should see "Are you sure you want to remove My Document 3?"
    And I press "Remove" in the "Consultation documents" region
    And I wait for AJAX to finish
    And I press "Save"
    Then I should see "Consultation Consultation demo page has been updated."
    And the General Contact entity with title "A general contact" exists
    And the "Document reference" sub-entity with title "Document reference to My Document 3" exists
