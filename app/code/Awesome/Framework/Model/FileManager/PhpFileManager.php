<?php
declare(strict_types=1);

namespace Awesome\Framework\Model\FileManager;

use Awesome\Framework\Exception\FileSystemException;

class PhpFileManager extends \Awesome\Framework\Model\FileManager
{
    /**
     * Include and parse PHP array file.
     * @param string $path
     * @param bool $graceful
     * @return array
     * @throws FileSystemException
     * @throws \RuntimeException
     */
    public function parseArrayFile(string $path, bool $graceful = false): array
    {
        if (!is_file($path)) {
            if (!$graceful) {
                throw new FileSystemException(__('Provided path "%1" does not exist or is not a file and cannot be parsed', $path));
            }
            $array = [];
        } else {
            $array = include $path;

            if (!is_array($array)) {
                throw new \RuntimeException(__('Provided path "%1" does not contain valid PHP array', $path));
            }
        }

        return $array;
    }

    /**
     * Include PHP file.
     * @param string $path
     * @param bool $return
     * @param bool $graceful
     * @return void|string
     * @throws FileSystemException
     */
    public function includeFile(string $path, bool $return = false, bool $graceful = false)
    {
        if (!is_file($path)) {
            if (!$graceful) {
                throw new FileSystemException(__('Provided path "%1" does not exist or is not a file and cannot be included', $path));
            }

            if ($return) {
                return '';
            }
        } else {
            if ($return) {
                ob_start();
                include $path;

                return ob_get_clean();
            }

            include $path;
        }
    }

    /**
     * Generate PHP array file.
     * According to short array syntax.
     * @param string $path
     * @param array $data
     * @param string $annotation
     * @return bool
     * @throws FileSystemException
     */
    public function createArrayFile(string $path, array $data, string $annotation = ''): bool
    {
        $content = '<?php' . ($annotation ? ' /** ' . $annotation . ' */' : '') . "\n"
            . 'return ' . array_export($data, true) . ';' . "\n";

        return $this->createFile($path, $content, true);
    }
}
