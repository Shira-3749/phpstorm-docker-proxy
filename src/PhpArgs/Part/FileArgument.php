<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs\Part;

class FileArgument extends Argument
{
    /** @var bool */
    public $pipeable;

    function __construct(string $value, bool $pipeable)
    {
        parent::__construct($value);

        $this->pipeable = $pipeable;
    }
}
