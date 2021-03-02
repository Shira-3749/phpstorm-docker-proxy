<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

/**
 * @template T
 * @extends \ArrayIterator<T>
 */
class ListIterator extends \ArrayIterator
{
    /**
     * @return T|null
     */
    function consume()
    {
        if (!$this->valid()) {
            return null;
        }

        $value = $this->current();
        $this->next();

        return $value;
    }
}
