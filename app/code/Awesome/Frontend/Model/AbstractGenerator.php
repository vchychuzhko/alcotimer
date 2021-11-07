<?php
declare(strict_types=1);

namespace Awesome\Frontend\Model;

use Awesome\Framework\Model\FileManager;
use Awesome\Frontend\Model\FrontendState;
use Awesome\Frontend\Model\StaticContent;

abstract class AbstractGenerator implements \Awesome\Frontend\Model\GeneratorInterface
{
    private const STATIC_FILE_PATTERN = '/.*\/app\/code\/(\w+)\/(\w+)\/view\/(\w+)\/web\/(.*)$/';

    /**
     * File extensions that corresponds to this generator.
     */
    protected static $extensions = [];

    /**
     * @var FileManager $fileManager
     */
    protected $fileManager;

    /**
     * @var FrontendState $frontendState
     */
    protected $frontendState;

    /**
     * AbstractGenerator constructor.
     * @param FileManager $fileManager
     * @param FrontendState $frontendState
     */
    public function __construct(
        FileManager $fileManager,
        FrontendState $frontendState
    ) {
        $this->fileManager = $fileManager;
        $this->frontendState = $frontendState;
    }

    /**
     * @inheritDoc
     */
    public static function match(string $path): bool
    {
        return in_array(pathinfo($path, PATHINFO_EXTENSION), static::$extensions, true);
    }

    /**
     * Get full file path in static folder path.
     * @param string $path
     * @param string $view
     * @param bool $direct
     * @return string
     */
    protected function getStaticPath(string $path, string $view, bool $direct = false): string
    {
        $staticPart = $direct ? $path : preg_replace(self::STATIC_FILE_PATTERN, '$1_$2/$4', $path);

        return BP . StaticContent::STATIC_FOLDER_PATH . $view . '/' . $staticPart;
    }
}
