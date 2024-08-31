<?php
declare(strict_types=1);

namespace Vch\Framework\Model\FileManager;

use Vch\Framework\Exception\FileSystemException;
use Vch\Framework\Exception\JsonValidationException;
use Vch\Framework\Model\Serializer\Json;

class JsonFileManager extends \Vch\Framework\Model\FileManager
{
    /**
     * @var Json $json
     */
    private $json;

    /**
     * JsonFileManager constructor.
     * @param Json $json
     */
    public function __construct(Json $json)
    {
        $this->json = $json;
    }

    /**
     * Read and parse JSON file.
     * @param string $path
     * @return mixed
     * @throws FileSystemException
     * @throws JsonValidationException
     */
    public function parseJsonFile(string $path)
    {
        try {
            return $this->json->decode($this->readFile($path));
        } catch (JsonValidationException $e) {
            throw new JsonValidationException(__('Provided file "%1" does not contain valid JSON', $path));
        }
    }
}
