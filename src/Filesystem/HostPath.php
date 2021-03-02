<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

class HostPath implements PathInterface
{
    /** @var string */
    private $path;

    /** @var string */
    private $hostPath;

    /** @var string */
    private $containerPath;

    /** @var string */
    private $directorySeparator;

    function __construct(string $path, string $hostPath, string $containerPath, string $directorySeparator)
    {
        $this->path = $path;
        $this->hostPath = $hostPath;
        $this->containerPath = $containerPath;
        $this->directorySeparator = $directorySeparator;
    }

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
