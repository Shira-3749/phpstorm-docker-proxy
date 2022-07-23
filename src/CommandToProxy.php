<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

use Shira\PhpStormDockerProxy\Config\Config;

class CommandToProxy {

    private string $command;

    public function __construct(string $command) {
        $this->command = $command;
    }

    public function getPath(Config $config) {
        if (isset($config->{$this->command . 'Bin'})) {
            return $config->{$this->command . 'Bin'};
        } elseif (isset($config->vendorBin)) {
            return $config->vendorBin . '/' . $this->command;
        }
        return $this->command;
    }
}