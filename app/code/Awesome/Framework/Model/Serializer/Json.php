<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Serializer;

class Json implements \Awesome\Framework\Model\SerializerInterface
{
    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function encode($data): string
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
    public function decode(string $string)
    {
        $result = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to decode value. Error: ' . json_last_error_msg());
        }

        return $result;
    }

    /**
     * Serialize data to json string with indentation and not escaped slashes.
     * @param array $data
     * @param int $options
     * @return string
     * @throws \InvalidArgumentException
     */
    public function prettyEncode(array $data, int $options = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT): string
    {
        $result = json_encode($data, $options);

        if ($result === false) {
            throw new \InvalidArgumentException('Unable to encode value. Error: ' . json_last_error_msg());
        }

        return $result;
    }
}
