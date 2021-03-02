<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class Argument implements PartInterface
{
    /** @var string */
    public $value;

    function __construct(string $value)
    {
        $this->value = $value;
    }

    function __toString(): string
    {
        return $this->value;
    }
}
