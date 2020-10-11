<?php

namespace Awesome\Framework\Model\FileManager;

class PhpFileManager extends \Awesome\Framework\Model\FileManager
{
    /**
     * Include PHP file.
     * @param string $path
     * @param bool $return
     * @param array $extract
     * @return string|array|void
     * @throws \RuntimeException
     */
    public function includeFile($path, $return = false, $extract = [])
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" does not exist', $path));
        }
        if (is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot be included', $path));
        }

        if ($extract) {
            extract($extract, EXTR_OVERWRITE);
        }

        if ($return) {
            return include $path;
        }

        include $path;
    }

    /**
     * Generate PHP array file.
     * @param string $path
     * @param array $data
     * @param string $annotation
     * @return bool
     * @throws \RuntimeException
     */
    public function createArrayFile($path, $data, $annotation = '')
    {
        $content = '<?php' . ($annotation ? ' /** ' . $annotation . ' */' : '') . "\n"
            . 'return ' . array_export($data, true) . ';' . "\n";

        return $this->createFile($path, $content, true);
    }

    /**
     * Check if requested PHP object have corresponding file.
     * @param string $objectName
     * @return bool
     * @throws \RuntimeException
     */
    public function objectFileExists($objectName)
    {
        $path = APP_DIR . '/' . str_replace('\\', '/', ltrim($objectName, '\\')) . '.php';

        if (is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot contain object', $path));
        }

        return file_exists($path);
    }
}
