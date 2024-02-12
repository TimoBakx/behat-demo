@resetDatabase
Feature: Logging in

  Scenario: As a user, I can log in
    Given there is a user "testuser@timobakx.dev"
    When I send a POST request to "/jwt/token" with body:
    """
    {
      "email": "testuser@timobakx.dev",
      "password": "testtest"
    }
    """
    Then the response status code should be 200
    And the response should be in JSON

  Scenario: As a visitor, I cannot login without account
    Given there is a user "testuser@timobakx.dev"
    When I send a POST request to "/jwt/token" with body:
    """
    {
      "email": "non-existing@timobakx.dev",
      "password": "testtest"
    }
    """
    Then the response status code should be 401
    And the response should be in JSON

  Scenario: As a visitor, I cannot login with invalid credentials
    Given there is a user "testuser@timobakx.dev"
    When I send a POST request to "/jwt/token" with body:
    """
    {
      "email": "testuser@timobakx.dev",
      "password": "incorrect password"
    }
    """
    Then the response status code should be 401
    And the response should be in JSON
