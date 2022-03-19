<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Http\Response;

use Awesome\Framework\Model\Serializer\Json;

class JsonResponse extends \Awesome\Framework\Model\Http\Response
{
    private Json $json;

    /**
     * JsonResponse constructor.
     * @param Json $json
     * @param array|string $data
     * @param int $status
     * @param array $headers
     */
    public function __construct(Json $json, $data = [], int $status = self::SUCCESS_STATUS_CODE, array $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->json = $json;
        is_array($data) ? $this->setData($data) : $this->setContent($data);
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
    public function setData(array $data): self
    {
        $content = $this->json->encode($data);

        return $this->setContent($content);
    }

    /**
     * Get response content, decoding it.
     * @return array
     */
    public function getData(): array
    {
        return $this->json->decode($this->content);
    }
}
