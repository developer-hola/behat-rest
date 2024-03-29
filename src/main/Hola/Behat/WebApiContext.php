<?php
/**
 * <strong>Name :  WebApiContext</strong><br>
 * <strong>Desc :  Base Class to Web Api Context</strong><br>
 *
 * PHP version 5.5.20
 *
 * @category  BehatRest
 * @package   Hola\Behat
 * @author    Development <desarrollo@hola-internet.com>
 * @copyright 2015 Hola.com
 * @license   Apache 2 License http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   GIT: $Id$
 * @link      http://www.hola.com
 * @since     File available since Release 0.1.0
 */

namespace Hola\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use GuzzleHttp\Exception\RequestException;
use PHPUnit_Framework_Assert as Assertions;

/**
 * Class WebApiContext
 *
 * @category   Service
 * @subpackage WebApiContext
 * @author     Development <desarrollo@hola-internet.com>
 * @copyright  2015 Hola.com
 * @license    Apache 2 License http://www.apache.org/licenses/LICENSE-2.0.html
 * @version    Release: <0.1.0>
 * @link       http://www.hola.com
 * @since      Class available since Release 0.1.0
 */
class WebApiContext implements Context
{
    /**
     * Http response
     *
     * @access protected
     * @var Response
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
     * Placeholders
     *
     * @access protected
     * @var array
     */
    protected $placeHolders = array();


    /**
     * Send request by Guzzle
     *
     * @param string $method  method http
     * @param string $uri     uri service
     * @param array  $options options to request
     * @param array  $headers optional headers to request
     *
     * @access protected
     * @return void
     */
    protected function sendRequest($method, $uri, $options, $headers = array())
    {
        $request = null;
        // Headers need services
        if (empty($headers)) {
            $headers = array(
                'headers' => array('Accept'       => 'application/json',
                    'content-type' => 'application/json'
                )
            );
        }
        $options = array_merge($options, $headers);

        try {
            switch ($method) {
                case 'GET':
                    $request = $this->client->get($uri, $options);
                    break;
                case 'POST':
                    $request = $this->client->post($uri, $options);
                    break;
                case 'PUT':
                    $request = $this->client->put($uri, $options);
                    break;
                case 'PATCH':
                    $request = $this->client->patch($uri, $options);
                    break;
                case 'DELETE':
                    $request = $this->client->delete($uri, $options);
                    break;
            }

            $this->response = $request;
            // Control exception for http codes 4xx
        } catch (RequestException $exception) {
            $this->response = $exception->getResponse();
        }
    }

    /**
     * Replaces placeholders in provided text.
     *
     * @param string $string
     *
     * @return string
     */
    protected function replacePlaceHolder($string)
    {
        foreach ($this->placeHolders as $key => $val) {
            $string = str_replace($key, $val, $string);
        }

        return $string;
    }


    /**
     * Initializes context.
     *
     * @param string $baseUrl base url of service
     */
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $client = new Client(['base_uri' => $this->baseUrl]);
        $this->client = $client;
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method     request method
     * @param string $uri        relative url
     * @param string $identifier identifier
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" need GET "([^"]+)"$/
     */
    public function iSendARequestWithPreGet($method, $uri, $identifier = null)
    {
        $parts = parse_url($uri);

        if (!empty($identifier) && array_key_exists('query', $parts)) {
            $this->sendRequest('GET', $uri, array());
            $body = json_decode((string)$this->response->getBody(), true);
            $identifier = $body['items'][0][$identifier];
            $uri = $parts['path'] . "/$identifier";
        }

        $this->sendRequest($method, $uri, array());
    }

    /**
     * Sends HTTP request to specific relative URL.
     *
     * @param string $method     request method
     * @param string $uri        relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)"$/
     */
    public function iSendARequest($method, $uri)
    {
        $this->sendRequest($method, $this->baseUrl.$uri, array());
    }

    /**
     * Sends HTTP request to specific relative URL accepting text/html
     *
     * @param string $method request method
     * @param string $uri    relative url
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" Accept HTML$/
     */
    public function iSendARequestHTML($method, $uri)
    {
        $this->sendRequest(
            $method, $uri, array(), array(
                'headers' => array('Accept'       => 'text/html',
                    'content-type' => 'text/html'
                ))
        );
    }

    /**
     * Sends HTTP request to specific URL with field values from Table.
     *
     * @param string    $method     request method
     * @param string    $uri        relative uri
     * @param string    $identifier identifier to retrieve
     * @param TableNode $post       table of post values
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" need GET "([^"]*)" with values:$/
     */
    public function iSendARequestWithValuesWithPreGet($method, $uri, $identifier = null, TableNode $post)
    {
        $fields = array();

        $parts = parse_url($uri);

        if (!empty($identifier) && array_key_exists('query', $parts)) {
            $this->sendRequest('GET', $uri, array());
            $body = json_decode((string)$this->response->getBody(), true);
            $identifier = $body['items'][0][$identifier];
            $uri = $parts['path'] . "/$identifier";
        }

        $table = $post->getTable();

        foreach ($table as $key => $value) {
            if (sizeof($value) == 3) {
                if ($value[2] == "array" || $value[2] == "Array") {
                    $fields[$value[0]] = json_decode($value[1], true);
                } else {
                    settype($value[1], $value[2]);
                    $fields[$value[0]] = $value[1];
                }
            } else {
                settype($value[1], "string");
                $fields[$value[0]] = $value[1];
            }
        }

        $bodyOption = json_encode($fields);

        $this->sendRequest($method, $uri, array('body' => $bodyOption));
    }

    /**
     * Sends HTTP request to specific URL with field values from Table.
     *
     * @param string    $method request method
     * @param string    $uri    relative uri
     * @param TableNode $post   table of post values
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with values:$/
     */
    public function iSendARequestWithValues($method, $uri, TableNode $post)
    {
        $fields = array();

        $table = $post->getTable();

        foreach ($table as $key => $value) {
            if (sizeof($value) == 3) {
                if ($value[2] == "array" || $value[2] == "Array") {
                    $fields[$value[0]] = json_decode($value[1], true);
                } else {
                    settype($value[1], $value[2]);
                    $fields[$value[0]] = $value[1];
                }
            } else {
                settype($value[1], "string");
                $fields[$value[0]] = $value[1];
            }
        }

        $bodyOption = json_encode($fields);

        $this->sendRequest($method, $uri, array('body' => $bodyOption));
    }

    /**
     * Sends HTTP request to specific URL with raw body from PyString.
     *
     * @param string       $method request method
     * @param string       $uri    relative uri
     * @param PyStringNode $string request body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with body:$/
     */
    public function iSendARequestWithBody($method, $uri, PyStringNode $string)
    {
        $bodyOption = $this->replacePlaceHolder(trim($string));

        $this->sendRequest($method, $uri, array('body' => $bodyOption));

    }

    /**
     * Sends HTTP request to specific URL with form data from PyString.
     *
     * @param string       $method request method
     * @param string       $uri    relative uri
     * @param PyStringNode $body   request body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with form data:$/
     */
    public function iSendARequestWithFormData($method, $uri, PyStringNode $body)
    {
        $body = $this->replacePlaceHolder(trim($body));

        $fields = array();
        parse_str(implode('&', explode("\n", $body)), $fields);

        $this->sendRequest($method, $uri, array('body' => $fields));
    }

    /**
     * Sends HTTP request to specific URL with query params from PyString.
     *
     * @param string       $method request method
     * @param string       $uri    relative uri
     * @param PyStringNode $body   request body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" with query params:$/
     */
    public function iSendARequestWithQueryParams($method, $uri, PyStringNode $body)
    {
        $body = $this->replacePlaceHolder(trim($body));
        $fields = implode('&', explode("\n", $body));
        $this->sendRequest($method, $uri . '?' . $fields, array());
    }

    /**
     * Sends HTTP request to specific URL with query params from PyString.
     *
     * @param string       $method request method
     * @param string       $uri    relative uri
     * @param PyStringNode $body   request body
     *
     * @When /^(?:I )?send a ([A-Z]+) request to "([^"]+)" Accept HTML with query params:$/
     */
    public function iSendAHTMLRequestWithQueryParams($method, $uri, PyStringNode $body)
    {
        $body = $this->replacePlaceHolder(trim($body));
        $fields = implode('&', explode("\n", $body));
        $this->sendRequest(
            $method, $uri . '?' . $fields, array(), array(
                'headers' => array('Accept'       => 'text/html',
                    'content-type' => 'text/html'
                ))
        );
    }

    /**
     * Check that response is a JSON
     *
     * @throws \Exception
     *
     * @Then /^the response should be JSON$/
     */
    public function theResponseShouldBeJson()
    {
        $data = json_decode($this->response->getBody(true));

        if (empty($data)) {
            throw new \Exception("Response was not JSON\n" . $data);
        }
    }

    /**
     * Check that response is a HTML
     *
     * @throws \Exception
     *
     * @Then /^the response should be HTML$/
     */
    public function theResponseShouldBeHtml()
    {
        $content_type = $this->response->getHeader('Content-Type');
        if (!in_array('text/html', $content_type)) {
            throw new \Exception("Response was not HTML\n" . json_encode($content_type));
        }
    }

    /**
     * Checks that response has specific status code.
     *
     * @param string $httpStatus status code
     *
     * @throws \Exception
     *
     * @Then /^the response status code should be (\d+)$/
     */
    public function theResponseStatusCodeShouldBe($httpStatus)
    {
        if ((string)$this->response->getStatusCode() !== $httpStatus) {
            throw new \Exception(
                'HTTP code does not match ' . $httpStatus .
                ' (actual: ' . $this->response->getStatusCode() . ')'
            );
        }
    }

    /**
     * Check that response has a property
     *
     * @param string $propertyName name property
     *
     * @throws \Exception
     *
     * @Then /^the response has a "([^"]*)" property$/
     */
    public function theResponseHasAProperty($propertyName)
    {
        $body = json_decode((string)$this->response->getBody(), true);
        if (array_key_exists('items', $body)) {
            $data = $body['items'][0];
        } else {
            $data = $body;
        }

        if (!empty($data)) {
            if (!isset($data[$propertyName])) {
                throw new \Exception("Property '" . $propertyName . "' is not set!\n");
            }
        } else {
            throw new \Exception("Response was not JSON\n" . $this->response->getBody(true));
        }
    }


    /**
     * Check the property is equal to passed string
     *
     * @param string $propertyName  name property
     * @param string $propertyValue property value
     *
     * @throws \Exception
     *
     * @Then /^the "([^"]*)" property equals number (\d+)$/
     */
    public function thePropertyEqualsNumber($propertyName, $propertyValue)
    {
        $body = json_decode((string)$this->response->getBody(), true);
        if (array_key_exists('items', $body)) {
            $data = $body['items'][0];
        } else {
            $data = $body;
        }

        if (!empty($data)) {
            if (!isset($data[$propertyName])) {
                throw new \Exception("Property '" . $propertyName . "' is not set!\n");
            }
            if ($data[$propertyName] !== (int)$propertyValue) {
                throw new \Exception(
                    'Property value mismatch! (given: ' . $propertyValue . ', match: ' . $data->$propertyName . ')'
                );
            }
        } else {
            throw new \Exception("Response was not JSON\n" . $this->response->getBody(true));
        }
    }

    /**
     * Check the property is equal to passed string
     *
     * @param string $propertyName  name property
     * @param string $propertyValue property value
     *
     * @throws \Exception
     *
     * @Then /^the "([^"]*)" property equals "([^"]*)"$/
     */
    public function thePropertyEquals($propertyName, $propertyValue)
    {
        $body = json_decode((string)$this->response->getBody(), true);
        if (array_key_exists('items', $body)) {
            $data = $body['items'][0];
        } else {
            $data = $body;
        }

        if (!empty($data)) {
            if (!isset($data[$propertyName])) {
                throw new \Exception("Property '" . $propertyName . "' is not set!\n");
            }
            if ($data[$propertyName] != $propertyValue) {
                throw new \Exception(
                    'Property value mismatch! (given: ' . $propertyValue . ', match: ' . $data->$propertyName . ')'
                );
            }
        } else {
            throw new \Exception("Response was not JSON\n" . $this->response->getBody(true));
        }
    }

    /**
     * Checks that response body contains JSON from PyString.
     *
     * Do not check that the response body /only/ contains the JSON from PyString,
     *
     * @param PyStringNode $jsonString
     *
     * @throws \RuntimeException
     *
     * @Then /^(?:the )?response should contain json:$/
     */
    public function theResponseShouldContainJson(PyStringNode $jsonString)
    {
        $etalon = json_decode($this->replacePlaceHolder($jsonString->getRaw()), true);
        $actual = json_decode((string)$this->response->getBody(), true);

        if (null === $etalon) {
            throw new \RuntimeException(
                "Can not convert etalon to json:\n" . $this->replacePlaceHolder($jsonString->getRaw())
            );
        }

        Assertions::assertGreaterThanOrEqual(count($etalon), count($actual));
        foreach ($etalon as $key => $needle) {
            Assertions::assertArrayHasKey($key, $actual);
            Assertions::assertEquals($etalon[$key], $actual[$key]);
        }
    }

    /**
     * Checks that response body contains X items
     *
     * @param integer $items items response
     *
     * @throws \Exception
     *
     * @Then /^the response contains (\d+) item(s)$/
     */
    public function theResponseContainsItems($items)
    {
        $body = json_decode((string)$this->response->getBody(), true);
        if (array_key_exists('items', $body)) {
            $data = $body['items'];
        } else {
            $data = $body;
        }

        if (!empty($data)) {
            if (count($data) != $items) {
                throw new \Exception("No contains $items items");
            }
        } else {
            throw new \Exception("Response was not JSON\n" . $this->response->getBody(true));
        }
    }

    /**
     * @Given /^the response should contain pagination keys$/
     */
    public function theResponseShouldContainPaginationKeys()
    {
        $body = json_decode((string)$this->response->getBody(), true);
        if (!array_key_exists('total', $body)
            || !array_key_exists('numRows', $body)) {

            throw new \Exception("Pagination fields not found!");
        }
    }
}
