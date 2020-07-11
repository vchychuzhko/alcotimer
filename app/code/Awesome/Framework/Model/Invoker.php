<?php

namespace Awesome\Framework\Model;

final class Invoker extends \Awesome\Framework\Model\Singleton
{
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

        if (is_a($id, Singleton::class, true)) {
            $object = $id::getInstance();
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
        }

        return $object;
    }
}
