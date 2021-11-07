<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Serializer;

use Awesome\Framework\Exception\JsonValidationException;

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
            throw new \InvalidArgumentException(__('Unable to encode value. Error: %1', json_last_error_msg()));
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @throws JsonValidationException
     */
    public function decode(string $string)
    {
        $result = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonValidationException(__('Unable to decode value. Error: %1', json_last_error_msg()));
        }

        return $result;
    }

    /**
     * Serialize data to json string with indentation and not escaped slashes or unicode.
     * @param array $data
     * @param int $options
     * @return string
     * @throws \InvalidArgumentException
     */
    public function prettyEncode(array $data, int $options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
    {
        $result = json_encode($data, $options);

        if ($result === false) {
            throw new \InvalidArgumentException(__('Unable to encode value. Error: %1', json_last_error_msg()));
        }

        return $result;
    }
}
