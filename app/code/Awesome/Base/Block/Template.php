<?php

namespace Awesome\Base\Block;

class Template
{
    /**
     * @var \Awesome\Cache\Model\StaticContent $staticContent
     */
    protected $staticContent;

    /**
     * @var string $template
     */
    protected $template;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->staticContent = new \Awesome\Cache\Model\StaticContent();
    }

    /**
     *
     * @return string
     */
    public function toHtml() {
        return include($this->template);
    }

    /**
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return '/' . PUB_DIR . 'media';
    }

    /**
     *
     * @return string
     */
    public function getStaticUrl()
    {
        if (!$deployedVersion = $this->staticContent->getDeployedVersion()) {
            $deployedVersion = $this->staticContent->deploy()
                ->getDeployedVersion();
            //@TODO: Resolve situation when frontend folder is missing, but deployed version is present
        }

        return '/' . PUB_DIR . 'static/version' . $deployedVersion . '/';
    }

    /**
     *
     * @param string $path
     * @param string $view
     * @param string $type
     * @return string
     */
    public function resolveXmlPath($path, $view, $type)
    {
        @list($module, $file) = explode('::', $path);
        $path = $module;

        if (isset($file)) {
            $module = str_replace('_', '/', $module);
            $path = $this->getStaticUrl() . $view . '/' . $module . '/' . $type . '/' . $file;
        }

        return $path;
    }

    /**
     * Template data getter.
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
     * Template data setter.
     *
     * The $key parameter can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function setData($key, $value = null)
    {
        if ($key === (array)$key) {
            $this->data = $key;
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Set/Get attribute wrapper.
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
                //vendor/magento/framework/DataObject.php - L381
        }

        throw new \Exception(
            'Invalid method ' . get_class($this) . '::' . $method
        );
    }

    /**
     * Converts field names for setters and getters.
     * $this->setMyField($value) === $this->setData('my_field', $value)
     *
     * @param string $name
     * @return string
     */
    protected function underscore($name)
    {
        $result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));

        return $result;
    }
}
