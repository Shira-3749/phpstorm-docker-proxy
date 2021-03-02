PhpStorm docker proxy
#####################

Run PHP from PhpStorm inside an existing Docker container.

.. contents::


Requirements
************

- PHP 7.1+ (installed locally)
- Docker
- PhpStorm


Supported use cases
*******************

- running PHP scripts using the *Run* function
- running PHPUnit tests


Installation
************

1. Get the phar
===============

Download the latest phar from `releases <https://github.com/ShiraNai7/phpstorm-docker-proxy/releases>`_.

You can also build your own. See `Building the phar`_.


.. TIP::

   An alternative is to clone this repository and use ``bin/phpstorm-docker-proxy``
   instead of ``phpstorm-docker-proxy.phar``.

   You will need `Composer <https://getcomposer.org/>`_ to install dependencies.


2. Create the config file
=========================

Create a file called ``.phpstorm-docker-proxy.json`` in your project's
root (or any parent directory) with the following contents:

.. code:: json

   {
       "image": "php_image_name_here",
       "paths": {
           "./app": "/var/www/html"
       }
   }

- change ``php_image_name_here`` to the name of your project's PHP Docker image
  (i.e. an image that contains a ``php`` binary executable from CLI)
- update ``paths`` to map local paths into the container
  (according to your docker volumes)

See more options in `List of configuration directives`_.


3. Configure PhpStorm to use the proxy
======================================

1. make sure the container specified in the configuration is up and running
2. head to *File - Settings - Languages & Frameworks - PHP*
3. click the "*...*" on the right side of *CLI Interpreter*
4. click the "*+*" to add a new interpreter as *Other Local...*
5. set *PHP executable* to a full path to ``phpstorm-docker-proxy.phar``
6. PhpStorm should check and display the expected PHP version

See `Troubleshooting tips`_ if you have any issues.

.. NOTE::

   On Windows, unless you have configured *.phar* files to be executable,
   you will need to create and use a batch script instead of the phar as
   *PHP executable*.

   Example of ``phpstorm-docker-proxy.bat`` (assuming phar in the same dir):

   .. code:: batch

      @echo off
      php %~dp0phpstorm-docker-proxy.phar %*


List of configuration directives
********************************

The following directives are supported in ``.phpstorm-docker-proxy.json``:

================== ============ =============================================
Option             Default      Description
================== ============ =============================================
image              \-           PHP Docker image name
paths              ``{}``       Host to container path mapping.

                                - host paths are relative to the config file
                                - container paths should be absolute
                                - trailing slashes should be omitted
phpBin             ``"php"``    PHP binary name or path inside the container.
dockerBin          ``"docker"`` Docker binary name or path on host.
directorySeparator ``"/"``      Directory separator inside the container.
debug              ``false``    Toggle debugging output.
================== ============ =============================================


Troubleshooting tips
********************

- make sure the configured Docker container is running
- try to run a plain PHP script (using *Run - Run...*) and check the output for errors
- add ``"debug": true`` to configuration to display additional information


PHPUnit
=======

- make sure the PHPUnit version is properly detected in *File - Settings -
  Languages & Frameworks - PHP - Test Frameworks*


How does it work
****************

This tool uses ``docker exec`` to proxy PHP calls from PhpStorm into
a running Docker container.

The rough workflow is as follows:

1. locate and load configuration file from working directory or above
2. parse the provided PHP arguments
3. extract ``IDE_*`` environment variables and try to resolve paths in them
4. use the ``IDE_*`` environment variables to guess what PhpStorm is trying to execute
5. process the PHP arguments so they're valid inside the container
6. locate a running container using the image name from configuration
7. run ``docker exec`` with appropriate options and arguments


Building the phar
*****************

Use the *build-phar.sh* script (available in source). You need to have
`Box <https://github.com/box-project/box>`_ installed
(either globally or as *box.phar* in the project's root directory).

.. code:: bash

   bin/build-phar.sh
