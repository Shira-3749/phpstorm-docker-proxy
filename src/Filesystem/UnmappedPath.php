<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

class UnmappedPath implements PathInterface
{
    function __construct(private string $path)
    {
    }

    function __toString(): string
    {
        return $this->path;
    }

    function translate(): string
    {
        return $this->path;
    }
}
