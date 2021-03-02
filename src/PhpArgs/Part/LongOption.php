<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class LongOption implements PartInterface
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $value;

    function __construct(string $name, ?string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    function __toString(): string
    {
        return $this->value !== null
            ? \sprintf('--%s=%s', $this->name, $this->value)
            : \sprintf('--%s', $this->name);
    }
}
