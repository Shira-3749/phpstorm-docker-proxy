<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

interface PathInterface extends \Stringable
{
    function translate(): string;
}
