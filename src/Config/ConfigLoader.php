<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Config;

use Shira\PhpStormDockerProxy\Utility as U;

class ConfigLoader
{
    private const FILE_NAME = '.phpstorm-docker-proxy.json';

    private const DEFAULTS = [
        'image' => null,
        'container' => null,
        'paths' => [],
        'phpBin' => 'php',
        'dockerBin' => 'docker',
        'directorySeparator' => '/',
        'debug' => false,
    ];

    function load(string $workingDir): Config
    {
        $path = $this->locate($workingDir);
        $baseDir = \dirname($path);

        \is_readable($path)
            or U::fail('Cannot read "%s"', $path);

        $data = \json_decode(\file_get_contents($path), true);

        \is_array($data)
            or U::fail('Could not parse config file "%s": %s', $path, \json_last_error_msg());

        $data = \array_replace_recursive(self::DEFAULTS, $data);

        $data['image'] !== null || $data['container'] !== null
            or U::fail('Image or container name is not specified');

        return new Config(
            $baseDir,
            $data['image'],
            $data['container'] ?: '',
            $this->resolvePaths($baseDir, $data['paths']),
            $data['phpBin'],
            $data['dockerBin'],
            $data['directorySeparator'],
            $data['debug']
        );
    }

    private function locate(string $dir): string
    {
        do {
            $lastDir = $dir;
            $configPath = $dir . DIRECTORY_SEPARATOR . self::FILE_NAME;

            if (\is_file($configPath)) {
                return $configPath;
            }
        } while (($dir = \dirname($dir)) !== $lastDir);

        U::fail('Could not locate "%s"', self::FILE_NAME);
    }

    /**
     * @param array<string, string> $paths
     * @return array<string, string>
     */
    private function resolvePaths(string $baseDir, array $paths): array
    {
        $resolvedPaths = [];

        foreach ($paths as $hostPath => $containerPath) {
            $realHostPath = \realpath($baseDir . DIRECTORY_SEPARATOR . $hostPath)
                or U::fail('Cannot resolve host path "%s"', $baseDir . DIRECTORY_SEPARATOR . $hostPath);

            $resolvedPaths[$realHostPath] = $containerPath;
        }

        return $resolvedPaths;
    }
}
