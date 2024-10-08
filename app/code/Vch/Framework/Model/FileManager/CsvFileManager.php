<?php
declare(strict_types=1);

namespace Vch\Framework\Model\FileManager;

use Vch\Framework\Exception\FileSystemException;
use Vch\Framework\Model\Serializer\Csv;

class CsvFileManager extends \Vch\Framework\Model\FileManager
{
    /**
     * Read and parse CSV file.
     * @param string $path
     * @param bool $graceful
     * @return array
     * @throws FileSystemException
     */
    public function parseFile(string $path, bool $graceful = false): array
    {
        $data = [];

        if (!is_file($path)) {
            if (!$graceful) {
                throw new FileSystemException(__('Provided path "%1" does not exist or is not a file and cannot be parsed', $path));
            }
        } else {
            $file = fopen($path, 'rb+');

            while (($line = fgetcsv($file, 0, Csv::DELIMITER, Csv::ENCLOSURE, Csv::ESCAPE_CHAR)) !== false) {
                $data[] = $line;
            }
        }

        return $data;
    }
}
