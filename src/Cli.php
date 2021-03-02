<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

use Shira\PhpStormDockerProxy\Config\ConfigLoader;
use Shira\PhpStormDockerProxy\Context\ContextProvider;
use Shira\PhpStormDockerProxy\Filesystem\PathHandler;
use Shira\PhpStormDockerProxy\PhpArgs\Parser;
use Shira\PhpStormDockerProxy\Utility as U;

class Cli
{
    /**
     * @param string[] $argv
     */
    function run(array $argv): int
    {
        try {
            $config = (new ConfigLoader())->load(\getcwd());
            U::setDebug($config->debug);

            $pathHandler = new PathHandler($config);
            $contextProvider = new ContextProvider($pathHandler);
            $args = (new Parser())->parse(\array_slice($argv, 1));

            $proxy = new Proxy(
                $config,
                $pathHandler,
                $contextProvider
            );

            U::debug('version: @git_commit@');
            U::debug('config: %s', $config);
            U::debug('raw env: %s', \getenv());
            U::debug('raw args: %s', $argv);
            U::debug('parsed args: %s', $args);

            return $proxy->run($args);
        } catch (FailureException $e) {
            U::err("Failure: %s", $e->getMessage());
            return 1;
        } catch (\Throwable $e) {
            U::err("Error: %s", (string) $e);
            return 255;
        }
    }
}
