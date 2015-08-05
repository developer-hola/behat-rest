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
