<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

abstract class Singleton
{
    protected static array $instances = [];

    /**
     * Singleton constructor.
     */
    protected function __construct() {}

    /**
     * Get Singleton instance.
     * @return $this
     */
    public static function getInstance(): self
    {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
        }

        return self::$instances[static::class];
    }
}
