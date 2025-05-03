@resetDatabase
Feature: Viewing a list of tasks

  Scenario: As a user, I can view a list of my tasks
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "testuser@timobakx.dev"
    And there is a task {9e31bfa1-2b61-47a4-b124-fc856fdef95c} owned by "testuser@timobakx.dev"
    And I am logged in as "testuser@timobakx.dev"
    When I send a GET request to "/tasks"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be valid according to the schema "features/tasks/schemas/list.json"
    And the JSON node "member" should have 2 elements

  Scenario: As a user, I cannot view the task of someone else in my list
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "testuser@timobakx.dev"
    And there is a task {50def79f-d472-4b08-bffe-407eb4ae668a} owned by "other-user@timobakx.dev"
    And I am logged in as "testuser@timobakx.dev"
    When I send a GET request to "/tasks"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be valid according to the schema "features/tasks/schemas/list.json"
    And the JSON node "member" should have 1 element

  Scenario: As a visitor, I cannot view a list of tasks
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "testuser@timobakx.dev"
    When I send a GET request to "/tasks"
    Then the response status code should be 401
    And the response should be in JSON
