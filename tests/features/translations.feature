@api
Feature: Content and interface translations.
  In order to visit the translated website
  As a visitor
  I should see the website text translated.

  Scenario: As a translator, I should see the date month and weekday names are translated.
    Given I am logged in as a user with the "translate interface, access administration pages, view the administration theme" permission
    When I am on "the interface translation page"
    And I fill in "String contains" with "Sunday"
    And I press "Filter"
    And print last response
    Then I should see "неделя"
    When I fill in "String contains" with "January"
    And I press "Filter"
    Then I should see "януари"
