<?php

namespace Awesome\Framework\Model;

class DataObject
{
    /**
     * @var array $data
     */
    protected $data;

    /**
     * DataObject constructor.
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->data = $data;
    }

    /**
     * DataObject data getter.
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the element specified by $key.
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key = '')
    {
        if ($key === '') {
            $data = $this->data;
        } else {
            $data = $this->data[$key] ?? null;
        }

        return $data;
    }

    /**
     * DataObject data setter.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value.
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if ($key === (array) $key) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Set/Get attribute wrapper.
     * vendor/magento/framework/DataObject.php - L381
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->underscore(substr($method, 3));

                return $this->getData($key);
            case 'set':
                $key = $this->underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;

                return $this->setData($key, $value);
        }

        throw new \Exception(
            'Invalid method ' . get_class($this) . '::' . $method
        );
    }

    /**
     * Converts camelCase to snake_case for setters and getters.
     * $this->getMyField() === $this->getData('my_field')
     *
     * @param string $string
     * @return string
     */
    protected function underscore($string)
    {
        return strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $string), '_'));
    }

    /**
     * Converts snake_case to camelCase for js widget configurations.
     *
     * @param string $string
     * @param string $separator
     * @return string
     */
    protected function camelCase($string, $separator = '_')
    {
        return str_replace($separator, '', lcfirst(ucwords($string, $separator)));
    }
}
