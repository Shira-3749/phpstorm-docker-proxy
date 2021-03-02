#!/usr/bin/env bash

if [ -x "$(command -v box)" ]; then
    BOX_COMMAND=box
elif [ -f box.phar ]; then
    BOX_COMMAND='php box.phar'
else
    echo Box is required to build the phar. See README.rst
    exit 1
fi

if ! [ -d vendor ]; then
    echo Vendor directory is missing. Did you run composer install?
    exit 1
fi

${BOX_COMMAND} compile
