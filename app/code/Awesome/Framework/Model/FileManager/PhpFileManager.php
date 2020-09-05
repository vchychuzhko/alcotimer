<?php

namespace Awesome\Framework\Model\FileManager;

class PhpFileManager extends \Awesome\Framework\Model\FileManager
{
    /**
     * Include PHP file.
     * @param string $path
     * @return string|array
     * @throws \RuntimeException
     */
    public function includeFile($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" does not exist', $path));
        }
        if (is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot be included', $path));
        }

        return include $path;
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
}
