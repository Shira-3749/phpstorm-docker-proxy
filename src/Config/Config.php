<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Config;

class Config
{
    /**
     * @param array<string, string> $paths
     */
    function __construct(
        public string $baseDir,
        public string $image,
        public array $paths,
        public string $phpBin,
        public string $dockerBin,
        public string $directorySeparator,
        public bool $debug
    ) {}
}
