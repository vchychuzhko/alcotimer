<?php

namespace Awesome\Framework\Model\Serializer;

class Json implements \Awesome\Framework\Model\SerializerInterface
{
    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function encode($data)
    {
        $result = json_encode($data);

        if ($result === false) {
            throw new \InvalidArgumentException('Unable to encode value. Error: ' . json_last_error_msg());
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function decode($string)
    {
        $result = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to decode value. Error: ' . json_last_error_msg());
        }

        return $result;
    }
}
