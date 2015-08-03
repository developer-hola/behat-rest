<?php


namespace Hola\Behat;

use Behat\Behat\Context\Context;
use GuzzleHttp\Client;

/**
 * Class FeatureContext
 *
 * @category   Service
 * @subpackage FeatureContext
 * @author     Development <desarrollo@hola-internet.com>
 * @copyright  ${YEAR} Hola.com
 * @license    Apache 2 License http://www.apache.org/licenses/LICENSE-2.0.html
 * @version    Release: <0.1.0>
 * @link       http://www.hola.com
 * @since      Class available since Release 0.1.0
 */
class FeatureContext implements Context
{
    /**
     * Http response
     *
     * @access protected
     * @var
     */
    protected $response;


    /**
     * Http Client
     *
     * @access protected
     * @var Client
     */
    protected $client;


    /**
     * Url Api to test
     *
     * @access protected
     * @var string
     */
    protected $baseUrl = array();


    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $client = new Client(['base_uri' => $this->baseUrl]);
        $this->client = $client;
    }

    /**
     * @When /^I request "([^"]*)"$/
     */
    public function iRequest($uri)
    {
        $request = $this->client->get($uri);
        $this->response = $request;
    }

    /**
     * @Then /^the response should be JSON$/
     */
    public function theResponseShouldBeJson()
    {
        $data = json_decode($this->response->getBody(true));
        if (empty($data)) { throw new Exception("Response was not JSON\n" . $this->response);
       }
    }

    /**
     * @Then /^the response status code should be (\d+)$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->response->getStatusCode() !== $httpStatus) {
            throw new \Exception('HTTP code does not match '.$httpStatus.
                ' (actual: '.$this->response->getStatusCode().')');
        }
    }

    /**
     * @Given /^the response has a "([^"]*)" property$/
     */
    public function theResponseHasAProperty($propertyName)
    {
        $data = json_decode($this->response->getBody(true));
        if (!empty($data)) {
            if (!isset($data->$propertyName)) {
                throw new Exception("Property '".$propertyName."' is not set!\n");
            }
        } else {
            throw new Exception("Response was not JSON\n" . $this->response->getBody(true));
        }
    }
     /**
     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
     */
    public function thePropertyEquals($propertyName, $propertyValue)
    {
        $data = json_decode($this->response->getBody(true));

        if (!empty($data)) {
            if (!isset($data->$propertyName)) {
                throw new Exception("Property '".$propertyName."' is not set!\n");
            }
            if ($data->$propertyName !== $propertyValue) {
                throw new \Exception('Property value mismatch! (given: '.$propertyValue.', match: '.$data->$propertyName.')');
            }
        } else {
            throw new Exception("Response was not JSON\n" . $this->response->getBody(true));
        }
    }
}
