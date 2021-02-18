<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\Serializer;

class Csv implements \Awesome\Framework\Model\SerializerInterface
{
    public const DELIMITER = ',';
    public const ENCLOSURE = '"';
    public const ESCAPE_CHAR = "\\";

    /**
     * @inheritDoc
     * @throws \InvalidArgumentException
     */
    public function encode($data): string
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Provided data is not an array and cannot be encoded to CSV');
        }
        $file = fopen('php://memory', 'r+');

        foreach ($data as $row) {
            fputcsv($file, $row, self::DELIMITER, self::ENCLOSURE, self::ESCAPE_CHAR);
        }
        rewind($file);

        return stream_get_contents($file);
    }

    /**
     * @inheritDoc
     */
    public function decode(string $string)
    {
        $data = [];
        $file = fopen('php://memory', 'r+');
        fwrite($file, $string);
        rewind($file);

        while (($line = fgetcsv($file, 0, self::DELIMITER, self::ENCLOSURE, self::ESCAPE_CHAR)) !== false) {
            $data[] = $line;
        }

        return $data;
    }
}
