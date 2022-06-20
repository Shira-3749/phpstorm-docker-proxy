<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class LongOption implements PartInterface
{
    function __construct(public string $name, public ?string $value)
    {
    }

    function __toString(): string
    {
        return $this->value !== null
            ? \sprintf('--%s=%s', $this->name, $this->value)
            : \sprintf('--%s', $this->name);
    }
}
