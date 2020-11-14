<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

interface SerializerInterface
{
    /**
     * Serialize provided data.
     * @param array $data
     * @return string
     */
    public function encode(array $data): string;

    /**
     * Unserialize provided string.
     * @param string $string
     * @return array
     */
    public function decode(string $string): array;
}
