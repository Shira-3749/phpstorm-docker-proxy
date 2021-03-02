<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Context;

use Shira\PhpStormDockerProxy\Filesystem\PathHandler;

class DefaultContext implements ContextInterface
{
    /** @var PathHandler */
    private $pathHandler;

    function __construct(PathHandler $pathHandler)
    {
        $this->pathHandler = $pathHandler;
    }

    function matches(array $env): bool
    {
        return true;
    }

    function processScriptArgs(array $args): void
    {
        foreach ($args as $arg) {
            // try to convert all args that don't look like an option to container paths
            if ($arg->value !== '' && $arg->value[0] !== '-') {
                $arg->value = $this->pathHandler->toContainerPath($arg->value);
            }
        }
    }
}
