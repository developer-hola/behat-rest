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
use Symfony\Component\Yaml\Yaml;

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


$configuration = Yaml::parseFile('/../resources/config/config.yml');

$envConfiguration = Yaml::parseFile($configuration["CONFIGURATION_FILES_".strtoupper($php_env)]);

$vendorPath = $configuration["VENDOR_PATH"];
$mocksPath = $configuration["MOCKS_PATH"];

if(isset($envConfiguration["VENDOR_PATH"])){
    $vendorPath = $envConfiguration["VENDOR_PATH"];
}
if(isset($envConfiguration["MOCKS_PATH"])){
    $mocksPath = $envConfiguration["MOCKS_PATH"];
}
set_include_path(
    '.' .
    PATH_SEPARATOR . $vendorPath .
    PATH_SEPARATOR . $mocksPath .
    PATH_SEPARATOR . get_include_path()
);

$_SERVER['REQUEST_METHOD'] = 'GET';
