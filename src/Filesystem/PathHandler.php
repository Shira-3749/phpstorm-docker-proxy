<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Filesystem;

use Shira\PhpStormDockerProxy\Config\Config;

class PathHandler
{
    /** @var Config */
    private $config;

    function __construct(Config $config)
    {
        $this->config = $config;
    }

    function toContainerPath(string $path): string
    {
        return $this->tryGetContainerPath($path) ?? $path;
    }

    function tryGetContainerPath(string $path): ?string
    {
        $path = $this->resolvePath($path);

        switch (true) {
            case $path instanceof HostPath:
                return $path->translate();

            case $path instanceof ContainerPath:
                return (string) $path;

            default:
                return null;
        }
    }

    private function resolvePath(string $path): PathInterface
    {
        $realPath = \realpath($path);

        return $this->identifyPath($realPath !== false ? $realPath : $path);
    }

    private function identifyPath(string $path): PathInterface
    {
        foreach ($this->config->paths as $hostPath => $containerPath) {
            if (\strncasecmp($path, $hostPath, \strlen($hostPath)) === 0) {
                return new HostPath($path, $hostPath, $containerPath, $this->config->directorySeparator);
            }

            if (\strncasecmp($path, $containerPath, \strlen($containerPath)) === 0) {
                return new ContainerPath($path, $containerPath, $hostPath);
            }
        }

        return new UnmappedPath($path);
    }
}
