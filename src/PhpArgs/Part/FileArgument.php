<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class FileArgument extends Argument
{
    function __construct(string $value, public bool $pipeable)
    {
        parent::__construct($value);
    }
}
