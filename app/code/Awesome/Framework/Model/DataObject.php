<?php

namespace Awesome\Framework\Model;

use Awesome\Framework\Helper\DataHelper;

class DataObject
{
    /**
     * @var array $data
     */
    protected $data;

    /**
     * @var bool $readOnly
     */
    private $readOnly;

    /**
     * DataObject constructor.
     * @param array $data
     * @param bool $readOnly
     */
    public function __construct($data = [], $readOnly = false)
    {
        $this->data = $data;
        $this->readOnly = $readOnly;
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
        if (!$this->readOnly) {
            if ($key === (array) $key) {
                $this->data = $key;
            } else {
                $this->data[$key] = $value;
            }
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
     * @throws \LogicException
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = DataHelper::underscore(substr($method, 3));

                return $this->getData($key);
            case 'set':
                $key = DataHelper::underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;

                return $this->setData($key, $value);
        }

        throw new \LogicException(sprintf('Invalid method %s::%s', get_class($this), $method));
    }
}
