<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Exception\DIException;
use Awesome\Framework\Model\Singleton;

final class Invoker extends \Awesome\Framework\Model\Singleton
{
    /**
     * Create requested object instance.
     * Parameters can be passed manually as an array.
     * @param string $id
     * @param array $parameters
     * @return mixed
     */
    public function create(string $id, array $parameters = [])
    {
        $id = ltrim($id, '\\');

        if (is_subclass_of($id, Singleton::class)) {
            if (!empty($parameters)) {
                throw new DIException(__('Parameters cannot be applied to "%1" as it is a Singleton', $id));
            }

            return $id::getInstance();
        }

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

        return new $id(...$arguments);
    }

    /**
     * Get requested object instance.
     * Parameters can be passed manually as an array.
     * Creates it if not yet initialized.
     * @param string $id
     * @param array $parameters
     * @return mixed
     */
    public function get(string $id, array $parameters = [])
    {
        $id = ltrim($id, '\\');

        if (is_subclass_of($id, Singleton::class)) {
            if (!empty($parameters)) {
                throw new DIException(__('Parameters cannot be applied to "%1" as it is a Singleton', $id));
            }

            return $id::getInstance();
        }

        if (isset(self::$instances[$id]) && !empty($parameters)) {
            throw new DIException(__('Parameters cannot be applied to "%1" as its instance is already initialized', $id));
        }

        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = $this->create($id, $parameters);
        }

        return self::$instances[$id];
    }
}
