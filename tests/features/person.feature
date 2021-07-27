@api
Feature: Person content creation
  In order to have "Person" on the site
  As an editor
  I need to be able to create and see content of type "Person"

  @javascript
  @batch3
  Scenario: Creation of a Person content through the UI.
    Given I am logged in as a user with the "create oe_person content, access content, edit own oe_person content, view published skos concept entities, manage corporate content entities" permission
    And the following images:
      | name          | file           | alt                |
      | Image 1       | example_1.jpeg | Alternative text 1 |
      | Image 2       | example_1.jpeg | Alternative text 2 |
      | Contact image | example_1.jpeg | Alternative text 4 |
    And the following document:
      | name          | file          |
      | My Document 1 | sample.pdf    |
      | My Document 2 | document.pdf  |
      | My Document 3 | document2.pdf |
    And the following General Contact entity:
      | Name | A general contact in Organisation |
    And the following "Organisation" Content entity:
      | Title             | Organisation as a contact         |
      | Organisation type | EU organisation                   |
      | EU organisation   | Directorate-General for Budget    |
      | Contact           | A general contact in Organisation |
    And the following "Organisation" Content entity:
      | Title             | Organisation demo page         |
      | Organisation type | EU organisation                |
      | EU organisation   | Directorate-General for Budget |
    And the following "Publication" Content entity:
      | Title | Publication node in Person |

    # Create a "person" content, mandatory fields first.
    When I visit "the Person creation page"
    Then the Node title field should not exist
    And I fill in "Subject" with "financing"
    And I fill in "Teaser" with "Teaser text"
    And I select "EU institutions related person" from "What type of person are you adding?"
    And I fill in "First name" with "Firstname"
    And I fill in "Last name" with "Lastname"
    And I should have the following options for the "Gender" select:
      | - Select a value -  |
      | female              |
      | male                |
      | not stated          |
    And I select "not stated" from "Gender"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    When I press "Save"
    Then I should see "Firstname Lastname"
    And I should see the link "financing"
    And I should see "Teaser text"
    And I should see "EU institutions related person"
    And I should see "Firstname"
    And I should see "Lastname"
    And I should see "not stated"
    And I should see the link "Committee on Agriculture and Rural Development"
    # Optional fields.
    When I click "Edit"
    And I fill in "Introduction" with "Summary text"
    And I fill in "Navigation title" with "Navi title"
    And I fill in "Alternative title" with "Shorter title"
    And I fill in "Displayed name" with "Altered name"
    And I fill in "Use existing media" with "Image 1" in the "Person portrait photo" region
    And I fill in "Use existing media" with "Image 2" in the "Person Media" region
    And I fill in "Departments" with "European Patent Office"
    And I fill in "Transparency introduction" with "transparency-introduction text"
    And I fill in "URL" with "http://transparency.example.com" in the "Person transparency links" region
    And I fill in "Link text" with "Example link" in the "Person transparency links" region
    And I fill in "Biography introduction" with "Bio-intro"
    And I fill in "Label" with "Label 1" in the "first" "Biography" field element
    And I fill in "Title" with "Title 1" in the "first" "Biography" field element
    And I fill in "Content" with "Body 1" in the "first" "Biography" field element
    And I press "Add another item" in the "Person biography" region
    And I wait for AJAX to finish
    And I fill in "Label" with "Label 2" in the "second" "Biography" field element
    And I fill in "Title" with "Title 2" in the "second" "Biography" field element
    And I fill in "Content" with "Body 2" in the "second" "Biography" field element
    And I press "Add another item" in the "Person biography" region
    And I wait for AJAX to finish
    And I fill in "Label" with "Label 3" in the "third" "Biography" field element
    And I fill in "Title" with "Title 3" in the "third" "Biography" field element
    And I fill in "Content" with "Body 3" in the "third" "Biography" field element
    And I fill in "Use existing media" with "My Document 1" in the "Person CV upload" region
    And I fill in "Declaration of interests introduction" with "declaration text"
    And I fill in "Use existing media" with "My Document 2" in the "Person declaration of interests file" region
    And I fill in "URL" with "http://twitter.com" in the "Social media links" region
    And I fill in "Link text" with "Twitter" in the "Social media links" region
    And I select "Twitter" from "Link type"
    And I fill in "Redirect link" with "http://example.com"
    # Contact field.
    And I press "Add new contact"
    And I wait for AJAX to finish
    And I fill in "Name" with "Name of the contact" in the "Person contacts" region
    And I fill in "Organisation" with "Person contact organisation" in the "Person contacts" region
    And I fill in "Body text" with "Person contact body text" in the "Person contacts" region
    And I fill in "Website" with "http://www.example.com/person_contact" in the "Person contacts" region
    And I fill in "Email" with "person_contact@example.com" in the "Person contacts" region
    And I fill in "Phone number" with "0488779033" in the "Person contacts" region
    And I fill in "Mobile number" with "0488779034" in the "Person contacts" region
    And I fill in "Fax number" with "0488779035" in the "Person contacts" region
    And I select "Hungary" from "Country" in the "Person contacts" region
    And I wait for AJAX to finish
    And I fill in "Street address" with "Person contact street" in the "Person contacts" region
    And I fill in "Postal code" with "9000" in the "Person contacts" region
    And I fill in "City" with "Budapest" in the "Person contacts" region
    And I fill in "Office" with "Person contact office" in the "Person contacts" region
    And I fill in "URL" with "mailto:person_contact_social@example.com" in the "Contact social media links" region
    And I fill in "Link text" with "Person contact social link email" in the "Contact social media links" region
    And I fill in "Media item" with "Contact image" in the "Person contacts" region
    And I fill in "Caption" with "Person contact caption" in the "Person contacts" region
    And I fill in "Press contacts" with "http://example.com/press_contacts" in the "Person contacts" region
    And I fill in "URL" with "https://www.example.com/link" in the "Contact link" region
    And I fill in "Link text" with "Contact link" in the "Contact link" region
    And I press "Create contact"
    And I wait for AJAX to finish
    # Add an organisation as contact.
    And I select "Organisation" in the "Person contacts" region
    And I press "Add new contact"
    And I fill in "Name" with "Organisation contact name" in the "Person contacts" region
    And I fill in "Organisation" with "Organisation as a contact" in the "Person contacts" region
    # Create document reference to Document media.
    And I press "Add new document reference"
    And I wait for AJAX to finish
    And I fill in "Use existing media" with "My Document 3" in the "Person documents" region
    And I press "Create document reference"
    And I wait for AJAX to finish
    # Create document reference to Publication node.
    And I select "Publication" in the "Person documents" region
    And I press "Add new document reference"
    And I wait for AJAX to finish
    And I fill in "Publication" with "Publication node in Person" in the "Person documents" region
    And I press "Create document reference"
    And I wait for AJAX to finish
    # Jobs field.
    And I press "Add new person job"
    And I wait for AJAX to finish
    And I fill in "first" person job role reference field with "Advisor"
    And I fill in "Responsibilities assigned to the job" with "Responsibilities text"
    And I check "Acting role"
    And I press "Save"
    Then I should see "Altered name"
    And I should see "Navi title"
    And I should see "Shorter title"
    And I should see "Altered name"
    And I should see "Image 1"
    And I should see "Image 2"
    And I should see "European Patent Office"
    And I should see "transparency-introduction text"
    And I should see "Bio-intro"
    And I should see "Label 1"
    And I should see "Title 1"
    And I should see "Body 1"
    And I should see "Label 2"
    And I should see "Title 2"
    And I should see "Body 2"
    And I should see "Label 3"
    And I should see "Title 3"
    And I should see "Body 3"
    And I should see "My Document 1"
    And I should see "declaration text"
    And I should see "My Document 2"
    And I should see the link "Twitter"
    And I should see "http://example.com"
    And I should see the link "Example link"
    # Assert person contacts values.
    And I should see the text "Name of the contact"
    And I should see the text "Person contact body text"
    And I should see the text "Person contact organisation"
    And I should see the link "http://www.example.com/person_contact"
    And I should see the text "person_contact@example.com"
    And I should see the text "0488779033"
    And I should see the text "0488779034"
    And I should see the text "0488779035"
    And I should see the text "Person contact street"
    And I should see the text "Budapest"
    And I should see the text "9000"
    And I should see the text "Hungary"
    And I should see the link "Person contact social link email"
    And I should see the text "Person contact office"
    And I should see the link "Contact image"
    And I should see the text "Person contact caption"
    And I should see the link "http://example.com/press_contacts"
    And I should see the link "Contact link"
    And I should see the text "Organisation contact name"
    # Document references are shown.
    And I should see "document2.pdf"
    And I should see "Publication node in Person"
    And I should see the text "Advisor"
    And I should see the text "Responsibilities text"
    And the "Acting role field" element should contain "On"

    When I click "Edit"
    And I select "Person not part of the EU institutions" from "What type of person are you adding?"
    And I press "Save"
    Then I should see "The role \"(Acting) Advisor\" is not compatible with the type of person currently selected. Please edit the related job entry and fix its role accordingly."
    When I fill in "Organisation" with "Organisation demo page"
    And I press "Edit" in the "Person jobs" region
    And I fill in "Role" with "Person job role"
    And I press "Save"
    Then I should see "Organisation demo page"
    And I should not see the link "European Patent Office"
    And I should not see "Advisor"
    And I should see "Person job role"
    Then I should not see "Acting role"

  @javascript
  @batch1
  Scenario: Length limited fields are truncating characters exceeding the configured limit.
    Given I am logged in as a user with the "create oe_person content, access content, edit own oe_person content, view published skos concept entities, manage corporate content entities" permission
    When I visit "the Person creation page"
    And I should see the text "Content limited to 300 characters, remaining: 300" in the "teaser form element"
    And I should see the text "Content limited to 250 characters, remaining: 250" in the "summary form element"
    And I should see the text "Content limited to 170 characters, remaining: 170" in the "alternative title form element"
    And I should see the text "Content limited to 125 characters, remaining: 125" in the "first name form element"
    And I should see the text "Content limited to 125 characters, remaining: 125" in the "last name form element"
    And I fill in "Subject" with "financing"
    And I fill in "Introduction" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas felis leo, lobortis non eros in, consequat tempor est. Praesent sit amet sem eleifend, cursus arcu ac, eleifend nunc. Integer et orci sagittis, volutpat felis sit ametas Introduction. Text to remove"
    And I fill in "Alternative title" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullamsa Alternative title. Text to remove"
    And I fill in "Teaser" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin eu hendrerit lacus, vitae bibendum odio. Fusce orci purus, hendrerit a magna at nullam. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Lorem ipsum dolor sit amet Teaser. Text to remove"
    And I select "EU institutions related person" from "What type of person are you adding?"
    And I fill in "First name" with "Firstname"
    And I fill in "Last name" with "Lastname"
    And I select "male" from "Gender"
    And I fill in "Content owner" with "Committee on Agriculture and Rural Development"
    And I press "Save"
    # We assert that the extra characters are actually truncated from the end of the string.
    Then I should not see "Text to remove"
    And I should see "ametas Introduction."
    And I should see "nullamsa Alternative title."
    And I should see "amet Teaser."

  @javascript
  @batch2
  Scenario: Ensure that person job, contact and document reference entities are not deleted after removing from the node.
    Given I am logged in as a user with the "access content, view published skos concept entities, view published oe_contact" permission
    And the following General Contact entity:
      | Name | A general contact |
    And the following Default "Person job" sub-entity:
      | Name             | Default person job 1  |
      | Role reference   | Advisor      |
      | Acting role      | Yes                   |
      | Responsibilities | Responsibilities text |
    And the following document:
      | name        | file       |
      | My Document | sample.pdf |
    And the following Document "Document reference" sub-entity:
      | Name     | Document reference to My Document |
      | Document | My Document                       |
    And the following Person Content entity:
      | Title                               | Person demo page                  |
      | Summary                             | Person summary                    |
      | Contacts                            | A general contact                 |
      | What type of person are you adding? | eu                                |
      | First name                          | First                             |
      | Last name                           | Last                              |
      | Gender                              | not stated                        |
      | Jobs                                | Default person job 1              |
      | Documents                           | Document reference to My Document |
    When I am visiting the "First Last" content
    Then I should see "First Last"
    And I should see "A general contact"
    And I should see "Advisor"
    And I should see "sample.pdf"

    When I am logged in as a user with the "create oe_person content, access content, edit any oe_person content, view published skos concept entities, manage corporate content entities" permission
    And I am visiting the "First Last" content
    And I click "Edit"
    And I press "Remove" in the "Person contacts" region
    Then I should see "Are you sure you want to remove A general contact?"
    When I press "Remove" in the "Person contacts" region
    And I wait for AJAX to finish
    And I press "Remove" in the "Person jobs" region
    Then I should see "Are you sure you want to remove (Acting) Advisor?"
    When I press "Remove" in the "Person jobs" region
    And I wait for AJAX to finish
    And I press "Remove" in the "Person documents" region
    And I wait for AJAX to finish
    Then I should see "Are you sure you want to remove My Document?"
    When I press "Remove" in the "Person documents" region
    And I press "Save"
    Then I should see "Person First Last has been updated."
    And I should not see "A general contact"
    And I should not see "Advisor"
    And I should not see "sample.pdf"
    And the General Contact entity with title "A general contact" exists
    And the "Person job" sub-entity with title "Default person job 1" exists
    And the "Document reference" sub-entity with title "Document reference to My Document" exists
