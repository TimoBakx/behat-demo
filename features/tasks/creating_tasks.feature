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
    And print last JSON response
    And the JSON should be valid according to this schema:
    """
    {
      "type": "object",
      "properties": {
        "@context": { "type": "string" },
        "@id": { "type": "string" },
        "@type": { "type": "string" },
        "title": { "type": "string" },
        "dueDate": { "type": ["null", "string"] }
      },
      "required": ["@context", "@id", "@type", "title", "dueDate"],
      "additionalProperties": false
    }
    """
    And the newest task should be owned by "testuser@timobakx.dev"
