<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Config;

class Config
{
    /** @var string */
    public $baseDir;

    /** @var string */
    public $image;

    /** @var array<string, string> */
    public $paths;

    /** @var string */
    public $phpBin;

    /** @var string */
    public $dockerBin;

    /** @var string */
    public $directorySeparator;

    /** @var bool */
    public $debug;

    /**
     * @param array<string, string> $paths
     */
    function __construct(
        string $baseDir,
        string $image,
        array $paths,
        string $phpBin,
        string $dockerBin,
        string $directorySeparator,
        bool $debug
    ) {
        $this->baseDir = $baseDir;
        $this->image = $image;
        $this->paths = $paths;
        $this->phpBin = $phpBin;
        $this->dockerBin = $dockerBin;
        $this->directorySeparator = $directorySeparator;
        $this->debug = $debug;
    }
}
