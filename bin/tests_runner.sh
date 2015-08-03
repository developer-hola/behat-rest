#!/bin/bash

function checkRetval
{
    if [ $1 -ne 0 ];
    then
        red='\e[0;31m'
        NC='\e[0m'
        echo -e "${red}Error on $2 execution. Review the report${NC}"
        exit $1
    fi
}

# Change the following lines to fit your needs
PHPUNIT_PATH=vendor/bin/phpunit
BOOTSTRAP=src/test/bootstrap.php
CONFIGURATION=src/test/phpunit.xml
TESTS=src/test/
PHPMD_PATH=vendor/bin/phpmd
PHPMD_REPORTING_DIR=reports/detector/
PHPMD_REPORTING_FILE=index.html
PHPMD_RULES=codesize,unusedcode,naming

# Do not change the following lines unless you know what you are doing.
$PHPUNIT_PATH --stderr --bootstrap ${BOOTSTRAP} --configuration ${CONFIGURATION}  ${TESTS}
checkRetval $? "PHPUNIT"
if [ ! -d ${PHPMD_REPORTING_DIR} ]; then
    mkdir -p ${PHPMD_REPORTING_DIR}
fi
$PHPMD_PATH src/main/ html $PHPMD_RULES --reportfile ${PHPMD_REPORTING_DIR}${PHPMD_REPORTING_FILE}
checkRetval $? "PHPMD"






