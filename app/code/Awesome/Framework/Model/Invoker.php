<?php
declare(strict_types=1);

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
    private function __construct() {}

    /**
     * Get requested class instance.
     * Only object-like parameters are allowed in class constructors.
     * @param string $id
     * @return mixed
     * @throws \Exception
     */
    public function get(string $id)
    {
        $id = ltrim($id, '\\');

        if (isset(self::$instances[$id])) {
            $object = self::$instances[$id];
        } else {
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
     * Get requested class instance with passing non-object parameters.
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function make(string $id, array $parameters = [])
    {
        $id = ltrim($id, '\\');

        if (isset(self::$instances[$id])) {
            $object = self::$instances[$id];
        } else {
            $reflectionClass = new \ReflectionClass($id);
            $arguments = [];

            if ($constructor = $reflectionClass->getConstructor()) {
                foreach ($constructor->getParameters() as $parameter) {
                    $parameterName = $parameter->getName();

                    if (isset($parameters[$parameterName])) {
                        $arguments[] = $parameters[$parameterName];
                    } elseif ($type = $parameter->getClass()) {
                        $arguments[] = $this->get($type->getName());
                    } elseif ($parameter->isOptional()) {
                        $arguments[] = $parameter->getDefaultValue();
                    } else {
                        throw new \Exception(
                            sprintf('Parameter "%s" was not provided for "%s" constructor', $parameterName, $id)
                        );
                    }
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
    public static function getInstance(): self
    {
        if (!isset(self::$instances[self::class])) {
            self::$instances[self::class] = new self();
        }

        return self::$instances[self::class];
    }
}
