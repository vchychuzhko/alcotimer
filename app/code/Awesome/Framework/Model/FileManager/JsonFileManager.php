<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\FileManager;

use Awesome\Framework\Exception\FileSystemException;
use Awesome\Framework\Exception\JsonValidationException;
use Awesome\Framework\Model\Serializer\Json;

class JsonFileManager extends \Awesome\Framework\Model\FileManager
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
