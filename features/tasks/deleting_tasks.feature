@resetDatabase
Feature: Deleting tasks

  Scenario: As a user, I can delete my task
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "testuser@timobakx.dev"
    And I am logged in as "testuser@timobakx.dev"
    When I send a DELETE request to "/tasks/123e4567-e89b-12d3-a456-426614174000"
    Then the response status code should be 204
    And task {123e4567-e89b-12d3-a456-426614174000} should not exist

  Scenario: As a user, I cannot delete a task of someone else
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "other-user@timobakx.dev"
    And I am logged in as "testuser@timobakx.dev"
    When I send a DELETE request to "/tasks/123e4567-e89b-12d3-a456-426614174000"
    Then the response status code should be 404
    And the response should be in JSON
    And task {123e4567-e89b-12d3-a456-426614174000} should exist

  Scenario: As a visitor, I cannot delete a task
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "testuser@timobakx.dev"
    When I send a DELETE request to "/tasks/123e4567-e89b-12d3-a456-426614174000"
    Then the response status code should be 401
    And the response should be in JSON
    And task {123e4567-e89b-12d3-a456-426614174000} should exist
