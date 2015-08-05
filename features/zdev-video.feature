Feature: ZDev RestFul Api Testing with Behat
 
 As a behat user
 I want to test restful api of the ZDev

Scenario: ZDev behat Demo Api
  Given I send a GET request to "video-service/aggregatedVideos"
  Then the response should be JSON
  And the response status code should be 200