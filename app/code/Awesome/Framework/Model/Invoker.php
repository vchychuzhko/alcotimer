<?php

namespace Awesome\Framework\Model;

use Awesome\Framework\Model\SingletonInterface;

final class Invoker implements \Awesome\Framework\Model\SingletonInterface
{
    /**
     * @var array $instances
     */
    private static $instances = [];

    /**
     * Invoker constructor.
     */
    private function __construct() {
        self::$instances[self::class] = $this;
    }

    /**
     * Get requested class instance.
     * Only object-like parameters are allowed in class constructors.
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function get($id)
    {
        $id = ltrim($id, '\\');

        if (!$object = self::$instances[$id] ?? null) {
            $reflectionClass = new \ReflectionClass($id);
            $arguments = [];

            if ($constructor = $reflectionClass->getConstructor()) {
                foreach ($constructor->getParameters() as $parameter) {
                    if (!$type = $parameter->getClass()) {
                        throw new \Exception(sprintf(
                            'Parameter "%s" is not a valid object type for "%s" constructor',
                            $parameter->getName(),
                            $id
                        ));
                    }
                    $arguments[] = $this->get($type->getName());
                }
            }
            $object = new $id(...$arguments);

            if ($object instanceof SingletonInterface) {
                self::$instances[$id] = $object;
            }
        }

        return $object;
    }

    /**
     * Get DIContainer instance.
     * @return $this
     */
    public static function getInstance()
    {
        if (!isset(self::$instances[self::class])) {
            self::$instances[self::class] = new self();
        }

        return self::$instances[self::class];
    }
}
