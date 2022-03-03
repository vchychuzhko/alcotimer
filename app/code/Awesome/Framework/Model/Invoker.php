<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Exception\DIException;
use Awesome\Framework\Model\SingletonInterface;

final class Invoker implements \Awesome\Framework\Model\SingletonInterface
{
    private static array $instances = [];

    /**
     * Invoker constructor.
     */
    private function __construct() {}

    /**
     * Create requested class instance.
     * Non-object and extra parameters can be passed as an array.
     * Regardless of SingletonInterface mark new instance will be created.
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function create(string $id, array $parameters = [])
    {
        $id = ltrim($id, '\\');

        $reflectionClass = new \ReflectionClass($id);
        $arguments = [];

        if ($constructor = $reflectionClass->getConstructor()) {
            foreach ($constructor->getParameters() as $parameter) {
                $parameterName = $parameter->getName();

                if (isset($parameters[$parameterName])) {
                    $arguments[] = $parameters[$parameterName];
                    continue;
                }
                if (($type = $parameter->getType())
                    && get_class($type) === 'ReflectionNamedType' && !$type->isBuiltin()
                ) {
                    $arguments[] = $this->get($type->getName());
                    continue;
                }
                if ($parameter->isOptional()) {
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                throw new DIException(__('Parameter "%1" was not provided for "%2" constructor', $parameterName, $id));
            }
        }

        $object = new $id(...$arguments);

        if ($object instanceof SingletonInterface) {
            self::$instances[$id] = $object;
        }

        return $object;
    }

    /**
     * Get requested class instance.
     * Non-object and extra parameters can be passed as an array.
     * Creates it if not yet initialized.
     * @param string $id
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function get(string $id, array $parameters = [])
    {
        $id = ltrim($id, '\\');

        if (isset(self::$instances[$id])) {
            if (!empty($parameters)) {
                throw new DIException(__('Provided parameters cannot be applied to "%1" as its instance is already initialized', $id));
            }

            $object = self::$instances[$id];
        } else {
            $object = $this->create($id, $parameters);
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
