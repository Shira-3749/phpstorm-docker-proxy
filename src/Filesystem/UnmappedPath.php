<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

class UnmappedPath implements PathInterface
{
    /** @var string */
    private $path;

    function __construct(string $path)
    {
        $this->path = $path;
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
