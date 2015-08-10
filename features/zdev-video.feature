Feature: ZDev RestFul Api Testing with Behat
 
 As a behat user
 I want to test restful api of the ZDev

Scenario: Get sucessfull to video-service/aggregatedVideos
  When I send a GET request to "aggregatedVideos"
  Then the response should be JSON
  And the response status code should be 200
  And the response has a "items" property

Scenario: Get unsucessfull to video-service/images, needs params
  When I send a GET request to "images"
  Then the response should be JSON
  And the response status code should be 400
  And the response should contain json:
  """"
      {
        "error":{
          "http_code":400,
          "message":"Field id is mandatory",
          "code":"MissingParameters"
        }
      }
  """"
  And the response contains 1 items