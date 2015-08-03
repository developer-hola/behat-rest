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
PHPCS_PATH=/usr/bin/phpcs
PHPCS_SEVERITY=3
PHPCS_STANDARD=PEAR
PHPCS_EXTENSIONS=php
PHPCS_DIRECTORIES=src/main
PHPCS_REPORT=xml
PHPCS_REPORTING_DIR=reports/phpcs/
PHPCS_REPORT_FILE=${PHPCS_REPORTING_DIR}phpcs.xml
PHPCS_REPORT_HTML_FILE=${PHPCS_REPORTING_DIR}phpcs.html
XSLTPROC_COMMAND=/usr/bin/xsltproc
XSLT_TEMPLATE=src/site/phpcs/phpcs.xslt

if [ -d ${PHPCS_REPORTING_DIR} ]; then
    rm -rf ${PHPCS_REPORTING_DIR}
fi

mkdir -p ${PHPCS_REPORTING_DIR}

# Do not change the following lines unless you know what you are doing.
$PHPCS_PATH -ws --severity=$PHPCS_SEVERITY --standard=$PHPCS_STANDARD --extensions=$PHPCS_EXTENSIONS --report=$PHPCS_REPORT --report-file=$PHPCS_REPORT_FILE $PHPCS_DIRECTORIES
retval=$?

$XSLTPROC_COMMAND --output $PHPCS_REPORT_HTML_FILE $XSLT_TEMPLATE $PHPCS_REPORT_FILE
checkRetval $? "XSLTPROC"

checkRetval $retval "PHPCS"


