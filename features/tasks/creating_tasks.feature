@resetDatabase
Feature: Creating tasks

  Scenario: As a user, I can c reate a task
    Given there is a user "testuser@timobakx.dev"
    And I am logged in as "testuser@timobakx.dev"
    When I send a POST request to "/tasks" with body:
    """
    {
      "title": "A task I need to do",
      "dueDate": null
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be valid according to the schema "features/tasks/schemas/task.json"
    And the newest task should be owned by "testuser@timobakx.dev"
