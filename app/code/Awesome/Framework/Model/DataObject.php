<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

use Awesome\Framework\Exception\DIException;
use Awesome\Framework\Helper\DataHelper;

class DataObject
{
    /**
     * @var array $data
     */
    private $data;

    /**
     * @var bool $readOnly
     */
    private $readOnly;

    /**
     * DataObject constructor.
     * @param array $data
     * @param bool $readOnly
     */
    public function __construct(array $data = [], bool $readOnly = false)
    {
        $this->data = $data;
        $this->readOnly = $readOnly;
    }

    /**
     * DataObject data getter.
     *
     * If $key is not defined will return all the data as an array.
     * Otherwise it will return value of the property specified by $key.
     *
     * @param string $key
     * @return mixed
     */
    public function getData(string $key = '')
    {
        $data = $this->data;

        if ($key !== '') {
            $data = $data[$key] ?? null;
        }

        return $data;
    }

    /**
     * DataObject data setter.
     *
     * The $key parameter can be string, number or array.
     * If $key is a string, the property will be overwritten by $value.
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null): self
    {
        if (!$this->readOnly) {
            if (is_array($key)) {
                $this->data = $key;
            } else {
                $this->data[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Set/Get attribute wrapper.
     * @see \Magento\Framework\DataObject::__call()
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws DIException
     */
    public function __call(string $method, array $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = DataHelper::underscore(substr($method, 3));

                return $this->getData($key);
            case 'set':
                $key = DataHelper::underscore(substr($method, 3));
                $value = $args[0] ?? null;

                return $this->setData($key, $value);
        }

        throw new DIException(__('Invalid method %1::%2', get_class($this), $method));
    }
}
