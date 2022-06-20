<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class Argument implements PartInterface
{
    function __construct(public string $value)
    {
    }

    function __toString(): string
    {
        return $this->value;
    }
}
