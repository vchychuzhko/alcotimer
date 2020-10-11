<?php

namespace Awesome\Framework\Model\Http\Response;

use Awesome\Framework\Model\Invoker;
use Awesome\Framework\Model\Serializer\Json;

class JsonResponse extends \Awesome\Framework\Model\Http\Response
{
    /**
     * @var Json $json
     */
    private $json;

    /**
     * JsonResponse constructor.
     * @param array|string $data
     * @inheritDoc
     */
    public function __construct($data = [], $status = self::SUCCESS_STATUS_CODE, $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->json = Invoker::getInstance()->get(Json::class);
        is_array($data) ? $this->setContentJson($data) : $this->setContent($data);
    }

    /**
     * @inheritDoc
     */
    public function proceed()
    {
        $this->setHeader('Content-Type', 'application/json');

        parent::proceed();
    }

    /**
     * Set response content, encoding it.
     * @param array $data
     * @return $this
     */
    public function setContentJson($data)
    {
        $content = $this->json->encode($data);

        return $this->setContent($content);
    }

    /**
     * Get response content, decoding it.
     * @return array
     */
    public function getContentJson()
    {
        return $this->json->decode($this->content);
    }
}
