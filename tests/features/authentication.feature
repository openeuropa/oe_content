Feature: User authentication
  In order to protect the integrity of the website
  As a product owner
  I want to make sure users with various roles can only access pages they are authorized to

  Scenario: Anonymous user can see the user login page
    Given I am not logged in
    When I visit "user"
    Then I should see the text "Log in"
    And I should see the text "Create new account"
    And I should see the text "Reset your password"
    And I should see the text "Username"
    And I should see the text "Password"
    But I should not see the text "Log out"
    And I should not see the text "View profile"
    