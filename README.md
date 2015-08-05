Installing Behat-Rest
---------------------

The easiest way to install Behat is by using [Composer](https://getcomposer.org):

<pre>
$> curl -sS https://getcomposer.org/installer | php
$> php composer.phar require --dev hola/behat-rest
</pre>

Configuration Behat-Rest
------------------------

Create directory called "features" in root directory, into features directory create file "behat.yml" contains configuration: 

<pre>
default:
  suites:
    default:
      contexts:
        - Hola\Behat\WebApiContext:
          - https://url.api.to.test
      paths:
        - %paths.base%
</pre>

News Features
-------------

Create new features into features folder and execute with behat:

<pre>
vendor/bin/behat -c features/behat.yml features/
</pre>

Sample Feature
--------------

Feature: Testing sample REST services
    In order to maintain user information through the services
    as a service user
    I want to see if the services work as expected 

    Scenario: Creating a New Employee
        When I send a POST request to "/employee" with values:
            | employeeId | 007         |
            | name       | James Bond  |
            | age        | 27          |
        Then response code should be 200
            And the response should be "true"

    Scenario: Finding an Existing Employee
        When I send a GET request to "/employee/007"
        Then response code should be 200
            And the response should contain json:
            """"
            {
                "name": "James Bond",
                "age": "27"
            }
            """"
        And in the response there is no field called "gender"

    Scenario: Updating an Existing Employee
        When I send a PUT request to "/employee/007" with values:
            | age | 38 |
        Then response code should be 200
            And the response should be "true"

    Scenario: Deleting Existing and Non-existing Employees
        Given I send a DELETE request to "/employee/007"
        Then response code should be 200
            And the response should be "true"
        Given I send a DELETE request to "/employee/008"
        Then response code should be 400
            And the response should contain "Unable to delete because the employee does not exist."

