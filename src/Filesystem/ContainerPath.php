<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

class ContainerPath implements PathInterface
{
    /** @var string */
    private $path;

    /** @var string */
    private $containerPath;

    /** @var string */
    private $hostPath;

    function __construct(string $path, string $containerPath, string $hostPath)
    {
        $this->path = $path;
        $this->containerPath = $containerPath;
        $this->hostPath = $hostPath;
    }

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
