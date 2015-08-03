<?php
/**
 * <strong>Name : bootstrap.php</strong><br>
 * <strong>Desc : Bootstrapping application</strong><br>
 *
 * PHP version 5.3
 *
 * @category  Hello-canada
 * @package   Hello-canada
 * @author    Desarrollo <desarrollo@hola-internet.com>
 * @copyright 2013 Hola.com
 * @license   Apache 2 License http://www.apache.org/licenses/LICENSE-2.0.html
 * @version   GIT: $Id$
 * @link      http://www.hola.com
 * @since     File available since Release 0.1
 */

date_default_timezone_set('UTC');

putenv('PHP_ENV=testing');

$php_env = getenv('PHP_ENV');

if (empty($php_env)) {
    $php_env = 'testing';
    putenv("PHP_ENV={$php_env}");
}

// Checking composer autoload.
// Previous to ConfigMediator as the latter is spread across the former.
$loader = include __DIR__ . '/../../../vendor/autoload.php';

$loader->setUseIncludePath(true);

// Instantiating Config Mediator
$configuration = new Hola\Configuration\ConfigMediator(__DIR__ . "/../../../");

// Setting our common variables.
$configuration->pushConfigurationFromFiles(
    array(__DIR__ . "/../resources/config/config.yml"),
    array("common")
);

// Setting our environment variables.
$configuration->pushConfigurationFromFiles(
    array(
        $configuration->getProperty(
            "CONFIGURATION_FILES_".
            strtoupper($php_env)
        )
    ),
    array($php_env)
);

set_include_path(
    '.' .
    PATH_SEPARATOR . $configuration->getProperty("VENDOR_PATH") .
    PATH_SEPARATOR . $configuration->getProperty("MOCKS_PATH") .
    PATH_SEPARATOR . get_include_path()
);

$_SERVER['REQUEST_METHOD'] = 'GET';
