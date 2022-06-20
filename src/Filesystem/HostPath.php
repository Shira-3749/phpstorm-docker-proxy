<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

class HostPath implements PathInterface
{
    function __construct(
        private string $path,
        private string $hostPath,
        private string $containerPath,
        private string $directorySeparator
    ) {}

    function __toString(): string
    {
        return $this->path;
    }

    function translate(): string
    {
        $result = \str_replace($this->hostPath, $this->containerPath, $this->path);

        if (\DIRECTORY_SEPARATOR !== $this->directorySeparator) {
            $result = \str_replace(\DIRECTORY_SEPARATOR, $this->directorySeparator, $result);
        }

        return $result;
    }
}
