<?php
declare(strict_types=1);

namespace Vch\Framework\Model;

interface SerializerInterface
{
    /**
     * Serialize provided data.
     * @param mixed $data
     * @return string
     */
    public function encode($data): string;

    /**
     * Unserialize provided string.
     * @param string $string
     * @return mixed
     */
    public function decode(string $string);
}
