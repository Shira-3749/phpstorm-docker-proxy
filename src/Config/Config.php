<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Config;

class Config
{
    /**
     * @param array<string, string> $paths
     */
    function __construct(
        public array $configArray
    ) {
        foreach ($configArray as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
