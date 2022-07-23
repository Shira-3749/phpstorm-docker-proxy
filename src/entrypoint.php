#!/usr/bin/env php
<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

require  __DIR__ . '/../vendor/autoload.php';

$backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);

$commandToProxy = new CommandToProxy(str_replace('phpstorm-docker-proxy-', '', basename($backtrace[0]['file'])));

exit((new Cli($commandToProxy))->run($argv));
