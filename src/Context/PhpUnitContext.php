<?php declare(strict_types=1);

namespace Shira\PhpStormDockerProxy\Context;

use Shira\PhpStormDockerProxy\Filesystem\PathHandler;
use Shira\PhpStormDockerProxy\ListIterator;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/src/TextUI/CliArguments/Builder.php
 */
class PhpUnitContext implements ContextInterface
{
    /**
     * Map of long options that accept a value
     */
    private const LONG_OPTS_W_VALUE = [
        'atleast-version' => ['reqEqual' => false, 'isPath' => false],
        'prepend' => ['reqEqual' => false, 'isPath' => true],
        'bootstrap' => ['reqEqual' => false, 'isPath' => true],
        'cache-result-file' => ['reqEqual' => false, 'isPath' => true],
        'colors' => ['reqEqual' => true, 'isPath' => false],
        'columns' => ['reqEqual' => false, 'isPath' => false],
        'configuration' => ['reqEqual' => false, 'isPath' => true],
        'coverage-cache' => ['reqEqual' => false, 'isPath' => true],
        'coverage-filter' => ['reqEqual' => false, 'isPath' => true],
        'coverage-clover' => ['reqEqual' => false, 'isPath' => true],
        'coverage-cobertura' => ['reqEqual' => false, 'isPath' => true],
        'coverage-crap4j' => ['reqEqual' => false, 'isPath' => true],
        'coverage-html' => ['reqEqual' => false, 'isPath' => true],
        'coverage-php' => ['reqEqual' => false, 'isPath' => true],
        'coverage-text' => ['reqEqual' => true, 'isPath' => true],
        'coverage-xml' => ['reqEqual' => false, 'isPath' => true],
        'default-time-limit' => ['reqEqual' => false, 'isPath' => false],
        'exclude-group' => ['reqEqual' => false, 'isPath' => false],
        'extensions' => ['reqEqual' => false, 'isPath' => false],
        'filter' => ['reqEqual' => false, 'isPath' => false],
        'group' => ['reqEqual' => false, 'isPath' => false],
        'covers' => ['reqEqual' => false, 'isPath' => false],
        'uses' => ['reqEqual' => false, 'isPath' => false],
        'include-path' => ['reqEqual' => false, 'isPath' => true],
        'list-tests-xml' => ['reqEqual' => false, 'isPath' => true],
        'loader' => ['reqEqual' => false, 'isPath' => false],
        'log-junit' => ['reqEqual' => false, 'isPath' => true],
        'log-teamcity' => ['reqEqual' => false, 'isPath' => true],
        'order-by' => ['reqEqual' => false, 'isPath' => false],
        'printer' => ['reqEqual' => false, 'isPath' => false],
        'repeat' => ['reqEqual' => false, 'isPath' => false],
        'random-order-seed' => ['reqEqual' => false, 'isPath' => false],
        'testdox-group' => ['reqEqual' => false, 'isPath' => false],
        'testdox-exclude-group' => ['reqEqual' => false, 'isPath' => false],
        'testdox-html' => ['reqEqual' => false, 'isPath' => true],
        'testdox-text' => ['reqEqual' => false, 'isPath' => true],
        'testdox-xml' => ['reqEqual' => false, 'isPath' => true],
        'test-suffix' => ['reqEqual' => false, 'isPath' => false],
        'testsuite' => ['reqEqual' => false, 'isPath' => false],
        'whitelist' => ['reqEqual' => false, 'isPath' => false],
        'dump-xdebug-filter' => ['reqEqual' => false, 'isPath' => false],
    ];

    /**
     * Map of short options that accept a value
     *
     * Option => is path?
     */
    private const SHORT_OPTS_W_VALUE = [
        'd' => false,
        'c' => true,
    ];

    /** @var PathHandler */
    private $pathHandler;

    function __construct(PathHandler $pathHandler)
    {
        $this->pathHandler = $pathHandler;
    }

    function matches(array $env): bool
    {
        foreach ($env as $name => $value) {
            if (\strncmp($name, 'IDE_PHPUNIT_', 12) === 0) {
                return true;
            }
        }

        return false;
    }

    function processScriptArgs(array $args): void
    {
        $args = new ListIterator($args);

        while ($args->valid()) {
            $arg = $args->current();

            if (\strncmp($arg->value, '--', 2) === 0) {
                $this->processLongOption($args);
            } elseif (\strncmp($arg->value, '-', 1) === 0) {
                $this->processShortOption($args);
            } else {
                $this->processArg($args);
            }
        }
    }

    private function processLongOption(ListIterator $args): void
    {
        $arg = $args->consume();

        if (($equalPos = \strpos($arg->value, '=')) !== false) {
            // --option=value
            $name = \substr($arg->value, 2, $equalPos - 2);
            $value = \substr($arg->value, $equalPos + 1);

            if (self::LONG_OPTS_W_VALUE[$name]['isPath'] ?? false) {
                $arg->value = \sprintf('--%s=%s', $name, $this->pathHandler->toContainerPath($value));
            }
        } else {
            // --option [value]
            $name = \substr($arg->value, 2);

            if (
                ($info = self::LONG_OPTS_W_VALUE[$name] ?? null) !== null
                && !$info['reqEqual']
                && ($valueArg = $args->consume()) !== null
                && $info['isPath']
            ) {
                $valueArg->value = $this->pathHandler->toContainerPath($valueArg->value);
            }
        }
    }

    private function processShortOption(ListIterator $args): void
    {
        $arg = $args->consume();
        $name = $arg->value[1] ?? '';

        if (($isPath = self::SHORT_OPTS_W_VALUE[$name] ?? null) !== null) {
            if (\strlen($arg->value) > 2) {
                // -xvalue
                if ($isPath) {
                    $arg->value = \sprintf('-%s%s', $name, $this->pathHandler->toContainerPath(\substr($arg->value, 2)));
                }
            } else {
                // -x value
                $valueArg = $args->consume();

                if ($valueArg !== null && $isPath) {
                    $valueArg->value = $this->pathHandler->toContainerPath($valueArg->value);
                }
            }
        }
    }

    private function processArg(ListIterator $args): void
    {
        $arg = $args->consume();
        $arg->value = $this->pathHandler->toContainerPath($arg->value);
    }
}
