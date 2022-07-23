<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

use Shira\PhpStormDockerProxy\Config\ConfigLoader;
use Shira\PhpStormDockerProxy\Filesystem\PathHandler;
use Shira\PhpStormDockerProxy\PhpArgs\Parser;
use Shira\PhpStormDockerProxy\Utility as U;

class Cli
{

    public CommandToProxy $commandToProxy;

    public function __construct(CommandToProxy $commandToProxy) {
        $this->commandToProxy = $commandToProxy;
    }

    /**
     * @param string[] $argv
     */
    function run(array $argv): int
    {
        try {
            $config = (new ConfigLoader())->load(\getcwd());
            U::setDebug($config->debug);

            $pathHandler = new PathHandler($config);
            $args = (new Parser())->parse(\array_slice($argv, 1));

            $proxy = new Proxy(
                $this->commandToProxy,
                $config,
                $pathHandler
            );

            U::debug('version: @git_commit@');
            U::debug('command to proxy: %s', $this->commandToProxy);
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
