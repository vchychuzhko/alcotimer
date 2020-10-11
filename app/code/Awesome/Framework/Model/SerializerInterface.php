<?php

namespace Awesome\Framework\Model;

interface SerializerInterface
{
    /**
     * Serialize provided data.
     * @param array $data
     * @return string
     */
    public function encode($data);

    /**
     * Unserialize provided string.
     * @param string $string
     * @return array
     */
    public function decode($string);
}
