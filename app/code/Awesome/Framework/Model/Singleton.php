<?php

namespace Awesome\Framework\Model;

abstract class Singleton
{
    /**
     * @var array $instances
     */
    private static $instances = [];

    /**
     * Singleton constructor.
     */
    protected function __construct() {}

    /**
     * Cloning is not allowed for singletons.
     */
    private function __clone() {}

    /**
     * Unserialization is not allowed for singletons.
     */
    private function __wakeup() {}

    /**
     * Get singleton's instance.
     * @return static
     * @throws \Exception
     */
    public static function getInstance()
    {
        // @TODO: Is a close duplicate for Invoker::get() method
        $className = static::class;

        if (!isset(self::$instances[$className])) {
            $reflectionClass = new \ReflectionClass($className);
            /** @var \ReflectionMethod $constructor */
            $constructor = $reflectionClass->getConstructor();
            $arguments = [];

            if ($parameters = $constructor->getParameters()) {
                $invoker = Invoker::getInstance();

                foreach ($parameters as $parameter) {
                    if (!$type = $parameter->getClass()) {
                        throw new \Exception(sprintf(
                            'Parameter "%s" is not a valid object type for "%s" constructor',
                            $parameter->getName(),
                            $className
                        ));
                    }
                    $arguments[] = $invoker->get($type->getName());
                }
            }

            self::$instances[$className] = new static(...$arguments);
        }

        return self::$instances[$className];
    }
}
