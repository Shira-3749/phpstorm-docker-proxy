<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

use Shira\PhpStormDockerProxy\Config\Config;
use Shira\PhpStormDockerProxy\Context\ContextInterface;
use Shira\PhpStormDockerProxy\Context\ContextProvider;
use Shira\PhpStormDockerProxy\Filesystem\PathHandler;
use Shira\PhpStormDockerProxy\PhpArgs\Part;
use Shira\PhpStormDockerProxy\Utility as U;

class Proxy
{
    /** @var Config */
    private $config;

    /** @var PathHandler */
    private $pathHandler;

    /** @var ContextProvider */
    private $contextProvider;

    function __construct(Config $config, PathHandler $pathHandler, ContextProvider $contextProvider)
    {
        $this->config = $config;
        $this->pathHandler = $pathHandler;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @param Part\PartInterface[] $args
     */
    function run(array $args): int
    {
        $env = $this->getEnv();
        $context = $this->contextProvider->getContext($env);
        [$args, $inputFile] = $this->processArgs($args, $context);
        $containerId = $this->getContainerId();

        U::debug('processed args: %s', $args);

        return $this->dockerExec($containerId, $env, $args, $inputFile);
    }

    /**
     * @param Part\PartInterface[] $args
     */
    private function dockerExec(string $containerId, array $env, array $args, ?string $inputFile): int
    {
        // prepare docker exec arguments
        $execArgs = ['-i'];

        if (\function_exists('stream_isatty') && \stream_isatty(\STDIN)) {
            $execArgs[] = '-t';
        }

        foreach ($env as $name => $value) {
            $execArgs[] = '-e';
            $execArgs[] = \sprintf('%s=%s', $name, $value);
        }

        if (($containerWorkingDir = $this->pathHandler->tryGetContainerPath(\getcwd())) !== null) {
            $execArgs[] = '-w';
            $execArgs[] = $containerWorkingDir;
        }

        $execArgs[] = $containerId;
        $execArgs[] = $this->config->phpBin;

        if ($args) {
            foreach ($args as $arg) {
                $execArgs[] = (string) $arg;
            }
        }

        // run docker exec
        $command = \sprintf(
            '%s exec %s',
            \escapeshellcmd($this->config->dockerBin),
            \implode(' ', \array_map('escapeshellarg', $execArgs))
        );

        U::debug('input file: %s', $inputFile);
        U::debug('command: %s', $command);

        $process = \proc_open(
            $command,
            [
                ['file', $inputFile ?? 'php://stdin', 'r'],
                ['file', 'php://stdout', 'w'],
                ['file', 'php://stderr', 'w'],
            ],
            $pipes
        );

        \is_resource($process)
            or U::fail('Failed to create process with command: %s', $command);

        return \proc_close($process);
    }

    private function getContainerId(): string
    {
        $containerId = \exec('docker ps -q --filter ancestor=' . \escapeshellarg($this->config->image));

        \is_string($containerId) && \ctype_xdigit($containerId)
            or U::fail('Could not find running container for image %s', $this->config->image);

        return $containerId;
    }

    private function getEnv(): array
    {
        $env = [];

        // fetch special IDE_* env vars
        foreach (\getenv() as $name => $value) {
            if (\strncmp($name, 'IDE_', 4) === 0) {
                // try to resolve path in env value
                $env[$name] = $this->pathHandler->toContainerPath($value);
            }
        }

        return $env;
    }

    /**
     * @param Part\PartInterface[] $args
     * @return array{Part\PartInterface[], string|null}
     */
    private function processArgs(array $args, ContextInterface $context): array
    {
        $outIndex = 0;
        $outArgs = [];
        $scriptArgs = [];
        $inputFile = null;
        $argSeparatorOffset = null;
        $scriptArgsOffset = null;

        foreach ($args as $part) {
            switch (true) {
                case $part instanceof Part\Option:
                    $part = $this->processOption($part, $inputFile);
                    break;

                case $part instanceof Part\LongOption:
                    // do nothing with long options
                    break;

                case $part instanceof Part\FileArgument:
                    $part = $this->processFileArg($part, $inputFile);
                    break;

                case $part instanceof Part\ScriptArgument:
                    // remember offset of first script arg
                    if ($scriptArgsOffset === null) {
                        $scriptArgsOffset = $outIndex;
                    }

                    // gather script args for context processing
                    $scriptArgs[] = ($part = clone $part);
                    break;

                case $part instanceof Part\Argument:
                    // remember offset of arg separator
                    if ($argSeparatorOffset === null && $part->value === '--') {
                        $argSeparatorOffset = $outIndex;
                    }
                    break;

                default:
                    U::fault();
            }

            if ($part !== null) {
                $outArgs[$outIndex++] = $part;
            }
        }

        // make sure script args are separated if input file is used
        if ($inputFile !== null && $argSeparatorOffset === null && $scriptArgsOffset !== null) {
            \array_splice($outArgs, $scriptArgsOffset, 0, [new Part\Argument('--')]);
        }

        $context->processScriptArgs($scriptArgs);

        return [$outArgs, $inputFile];
    }

    private function processOption(Part\Option $option, ?string &$inputFile): ?Part\Option
    {
        $out = clone $option;

        if ($option->value !== null) {
            if ($option->name === 'f') {
                // handle -f
                if (($containerPath = $this->pathHandler->tryGetContainerPath($option->value)) !== null) {
                    // use container path
                    $out->value = $containerPath;
                } elseif (\is_file($option->value)) {
                    // pass as input file
                    $inputFile = $option->value;
                    $out = null;
                }
            } elseif ($option->isPath) {
                // handle path value
                $out->value = $this->pathHandler->toContainerPath($option->value);
            }
        }

        return $out;
    }

    private function processFileArg(Part\FileArgument $arg, ?string &$inputFile): ?Part\FileArgument
    {
        $out = clone $arg;

        if (($containerPath = $this->pathHandler->tryGetContainerPath($arg->value)) !== null) {
            // use container path
            $out->value = $containerPath;
        } elseif ($arg->pipeable && \is_file($arg->value)) {
            // pass as input file
            $inputFile = $arg->value;
            $out = null;
        }

        return $out;
    }
}
