<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\PhpArgs;

use Shira\PhpStormDockerProxy\ListIterator;

class Parser
{
    private const FILE_ARG_DEFAULT = 0;
    private const FILE_ARG_ROUTER = 1;
    private const FILE_ARG_NONE = 2;

    /**
     * Map of short options that accept a value
     *
     * Option => is path?
     */
    private const SHORT_OPTS_W_VAL = [
        'c' => true,
        'd' => false,
        'f' => true,
        'r' => false,
        'B' => false,
        'R' => false,
        'F' => true,
        'E' => false,
        'S' => false,
        't' => true,
        'z' => true,
    ];

    /**
     * Map of short options that override the file argument
     *
     * Option => mode
     */
    private const SHORT_OPTS_FILE_ARG_OVERRIDE = [
        'f' => self::FILE_ARG_NONE,
        'r' => self::FILE_ARG_NONE,
        'R' => self::FILE_ARG_NONE,
        'S' => self::FILE_ARG_ROUTER,
    ];

    /** Map of long options that accept a value */
    private const LONG_OPTS_W_VAL = [
        'rf' => true,
        'rc' => true,
        're' => true,
        'rz' => true,
        'ri' => true,
    ];

    /**
     * @param string[] $args
     * @return Part\PartInterface[]
     */
    function parse(array $args): array
    {
        return \iterator_to_array($this->scan(new ListIterator($args)), false);
    }

    private function scan(ListIterator $args): \Iterator
    {
        $fileArgMode = self::FILE_ARG_DEFAULT;

        while ($args->valid()) {
            $value = $args->current();
            $length = \strlen($value);

            if ($length >= 3 && \strncmp($value, '--', 2) === 0) {
                yield $this->parseLongOption($args);
            } elseif ($length >= 2 && $value[0] === '-' && $value[1] !== '-') {
                $option = $this->parseShortOption($args);

                if (isset(self::SHORT_OPTS_FILE_ARG_OVERRIDE[$option->name])) {
                    $fileArgMode = self::SHORT_OPTS_FILE_ARG_OVERRIDE[$option->name];
                }

                yield $option;
            } else {
                switch ($fileArgMode) {
                    // file arg, rest are script args
                    case self::FILE_ARG_DEFAULT:
                        yield new Part\FileArgument($args->consume(), true);
                        break 2;

                    // router script, rest are ignored script args
                    case self::FILE_ARG_ROUTER:
                        yield new Part\FileArgument($args->consume(), false);
                        break 2;
                }

                if ($value === '--') {
                    // arg separator
                    yield new Part\Argument($args->consume());
                }

                // rest are script args
                break;
            }
        }

        yield from $this->parseScriptArgs($args);
    }

    private function parseLongOption(ListIterator $args): Part\LongOption
    {
        $value = $args->consume();

        if (($equalPos = \strpos($value, '=')) !== false) {
            // --option=value
            return new Part\LongOption(\substr($value, 2, $equalPos - 2), \substr($value, $equalPos + 1));
        }

        return new Part\LongOption(
            $name = \substr($value, 2),
            isset(self::LONG_OPTS_W_VAL[$name])
                ? $args->consume() // --option value
                : null // --option
        );
    }

    private function parseShortOption(ListIterator $args): Part\Option
    {
        $value = $args->consume();
        $name = $value[1];

        if (($isPath = self::SHORT_OPTS_W_VAL[$name] ?? null) !== null) {
            if (\strlen($value) >= 3) {
                // -x=value or -xvalue
                return new Part\Option(
                    $name,
                    \substr(
                        $value,
                        $value[2] === '='
                            ? 3 // -x=value
                            : 2 // -xvalue
                    ),
                    $isPath
                );
            }

            // -x value
            return new Part\Option($name, $args->consume(), $isPath);
        }

        // -x
        return new Part\Option(\substr($value, 1), null);
    }

    private function parseScriptArgs(ListIterator $args): \Generator
    {
        for (; $args->valid(); $args->next()) {
            yield new Part\ScriptArgument($args->current());
        }
    }
}
