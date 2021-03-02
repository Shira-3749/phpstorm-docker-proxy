<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Context;

use Shira\PhpStormDockerProxy\Filesystem\PathHandler;
use Shira\PhpStormDockerProxy\Utility as U;

class ContextProvider
{
    /** @var ContextInterface[] */
    private $contexts;

    function __construct(PathHandler $pathHandler)
    {
        $this->contexts = [
            new PhpUnitContext($pathHandler),
            new DefaultContext($pathHandler),
        ];
    }

    function getContext(array $env): ContextInterface
    {
        foreach ($this->contexts as $context) {
            if ($context->matches($env)) {
                return $context;
            }
        }

        U::fault();
    }
}
