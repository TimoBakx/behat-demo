@resetDatabase
Feature: Editing tasks

  Background:
    Given there is a user "testuser@timobakx.dev"
    And there is a task {123e4567-e89b-12d3-a456-426614174000} owned by "testuser@timobakx.dev" with title "Original task"

  Scenario: As a user, I can edit my task
    Given I am logged in as "testuser@timobakx.dev"
    When I add "Content-Type" header equal to "application/merge-patch+json"
    And I send a PATCH request to "/tasks/123e4567-e89b-12d3-a456-426614174000" with body:
    """
    {
      "title": "Updated task"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be valid according to the schema "features/tasks/schemas/task.json"
    And the JSON node "title" should be equal to "Updated task"
    And the title of task {123e4567-e89b-12d3-a456-426614174000} should be "Updated task"

  Scenario: As a user, I cannot edit a task of someone else
    Given I am logged in as "other-user@timobakx.dev"
    When I add "Content-Type" header equal to "application/merge-patch+json"
    And I send a PATCH request to "/tasks/123e4567-e89b-12d3-a456-426614174000" with body:
    """
    {
      "title": "Updated task"
    }
    """
    Then the response status code should be 404
    And the response should be in JSON
    And the title of task {123e4567-e89b-12d3-a456-426614174000} should be "Original task"

  Scenario: As a visitor, I cannot edit a task
    When I add "Content-Type" header equal to "application/merge-patch+json"
    And I send a PATCH request to "/tasks/123e4567-e89b-12d3-a456-426614174000" with body:
    """
    {
      "title": "Updated task"
    }
    """
    Then the response status code should be 401
    And the response should be in JSON
    And the title of task {123e4567-e89b-12d3-a456-426614174000} should be "Original task"
