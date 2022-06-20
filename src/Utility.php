<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy;

use Kuria\Debug\Dumper;

abstract class Utility
{
    private static bool $debug = false;

    /**
     * Toggle debug messages
     */
    static function setDebug(bool $debug): void
    {
        self::$debug = $debug;
    }

    /**
     * Write a message to stderr
     */
    static function err(string $message, ...$args): void
    {
        \fwrite(\STDERR, '[PhpStormDockerProxy] ');
        \fwrite(\STDERR, \vsprintf($message, $args));
        \fwrite(\STDERR, "\n");
    }

    /**
     * Write a message to stderr if debug is enabled
     */
    static function debug(string $message, ...$args): void
    {
        if (!self::$debug) {
            return;
        }

        \array_walk($args, function (&$arg) {
            if (\is_bool($arg) || !\is_scalar($arg)) {
                $arg = Dumper::dump($arg, 3, 255);
            }
        });

        self::err($message, ...$args);
    }

    /**
     * Throw a failure exception
     *
     * @throws FailureException
     * @return no-return
     */
    static function fail(string $message, ...$args): void
    {
        throw new FailureException(\vsprintf($message, $args));
    }

    /**
     * Throw a logic exception
     *
     * @throws \LogicException
     * @return no-return
     */
    static function fault(string $message = '', ...$args): void
    {
        throw new \LogicException(\vsprintf($message, $args));
    }
}
