<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

interface PathInterface
{
    function __toString(): string;

    function translate(): string;
}
