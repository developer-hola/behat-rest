#!/bin/bash -e

# Change the following lines to fit your needs
PHPDOC_PATH=vendor/bin/phpdoc.php
PHPDOC_CONFIGURATION=src/site/phpdoc/phpdoc.xml

# Do not change the following lines unless you know what you are doing.
$PHPDOC_PATH -c $PHPDOC_CONFIGURATION