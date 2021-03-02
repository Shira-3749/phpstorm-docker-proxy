<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class Option implements PartInterface
{
    /** @var string */
    public $name;

    /** @var string|null */
    public $value;

    /** @var bool */
    public $isPath;

    function __construct(string $name, ?string $value, bool $isPath = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->isPath = $isPath;
    }

    function __toString(): string
    {
        return $this->value !== null
            ? \sprintf('-%s%s', $this->name, $this->value)
            : \sprintf('-%s', $this->name);
    }
}
