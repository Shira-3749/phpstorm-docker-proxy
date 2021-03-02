<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Context;

use Shira\PhpStormDockerProxy\PhpArgs\Part\ScriptArgument;

interface ContextInterface
{
    function matches(array $env): bool;

    /**
     * @param ScriptArgument[] $args
     */
    function processScriptArgs(array $args): void;
}
