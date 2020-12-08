<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Result;

use Awesome\Framework\Model\Serializer\Json;

class JsonResponse extends \Awesome\Framework\Model\Result\Response
{
    /**
     * @var Json $json
     */
    private $json;

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
        is_array($data) ? $this->setContentJson($data) : $this->setContent($data);
    }

    /**
     * @inheritDoc
     */
    public function proceed(): void
    {
        $this->setHeader('Content-Type', 'application/json');

        parent::proceed();
    }

    /**
     * Set response content, encoding it.
     * @param array $data
     * @return $this
     */
    public function setContentJson(array $data): self
    {
        $content = $this->json->encode($data);

        return $this->setContent($content);
    }

    /**
     * Get response content, decoding it.
     * @return array
     */
    public function getContentJson(): array
    {
        return $this->json->decode($this->content);
    }
}
