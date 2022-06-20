<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

class ContainerPath implements PathInterface
{
    function __construct(
        private string $path,
        private string $containerPath,
        private string $hostPath
    ) {}

    function __toString(): string
    {
        return $this->path;
    }

    function translate(): string
    {
        $result = \str_replace($this->containerPath, $this->hostPath, $this->path);
        $result = \str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $result);

        return $result;
    }
}
