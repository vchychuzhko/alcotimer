<?php

namespace Awesome\Framework\Model;

class FileManager
{
    private const DEFAULT_ACCESS_MODE = 0777;

    /**
     * Create file.
     * @param string $path
     * @param string $content
     * @param bool $replace
     * @param bool $recursively
     * @return bool
     * @throws \RuntimeException
     */
    public function createFile($path, $content = '', $replace = false, $recursively = true)
    {
        if (file_exists($path)) {
            if (is_dir($path)) {
                throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot be replaced', $path));
            }
            if (!$replace) {
                throw new \RuntimeException(sprintf('Provided file "%s" already exists and cannot be replaced', $path));
            }
        }

        if (!is_dir(dirname($path))) {
            if (!$recursively) {
                throw new \RuntimeException(sprintf('Directory does not exist for provided file "%s"', $path));
            }
            $this->createDirectory(dirname($path));
        }

        return @file_put_contents($path, $content) !== false;
    }

    /**
     * Read file.
     * @param string $path
     * @param bool $graceful
     * @return string
     * @throws \RuntimeException
     */
    public function readFile($path, $graceful = true)
    {
        if (!$graceful && !file_exists($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" does not exist', $path));
        }
        if (is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot be read', $path));
        }

        return (string) @file_get_contents($path);
    }

    /**
     * Write content to a file.
     * @param string $path
     * @param string $content
     * @param bool $append
     * @param bool $create
     * @return bool
     * @throws \RuntimeException
     */
    public function writeFile($path, $content, $append = false, $create = true)
    {
        if (is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot be written', $path));
        }
        if (!file_exists($path)) {
            if (!$create) {
                throw new \RuntimeException(sprintf('Provided file "%s" does not exist and cannot be written', $path));
            }
            $this->createFile($path);
        }

        return @file_put_contents($path, $content, $append ? FILE_APPEND : 0);
    }

    /**
     * Remove file.
     * @param string $path
     * @return bool
     * @throws \RuntimeException
     */
    public function removeFile($path)
    {
        if (is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is a directory and cannot be removed', $path));
        }

        return @unlink($path);
    }

    /**
     * Create directory.
     * @param string $path
     * @param bool $recursive
     * @param int $mode
     * @return bool
     * @throws \RuntimeException
     */
    public function createDirectory($path, $recursive = true, $mode = self::DEFAULT_ACCESS_MODE)
    {
        if (file_exists($path)) {
            if (!is_dir($path)) {
                throw new \RuntimeException(sprintf('File with provided directory name "%s" already exists', $path));
            }
        } elseif (!@mkdir($path, $mode, $recursive)) {
            throw new \RuntimeException(sprintf('Cannot create a directory "%s"', $path));
        }

        return true;
    }

    /**
     * Remove directory.
     * Based on https://www.php.net/manual/en/function.rmdir.php#117354
     * @param string $path
     * @param bool $recursively
     * @return bool
     * @throws \RuntimeException
     */
    public function removeDirectory($path, $recursively = true)
    {
        if (file_exists($path)) {
            if (!is_dir($path)) {
                throw new \RuntimeException(sprintf('Provided path "%s" is not a directory', $path));
            }
            if ($recursively) {
                foreach (scandir($path) as $object) {
                    if ($object !== '.' && $object !== '..') {
                        if (is_dir($path . '/' . $object)) {
                            $this->removeDirectory($path . '/' . $object);
                        } else {
                            unlink($path . '/' . $object);
                        }
                    }
                }
            }
        }

        return @rmdir($path);
    }

    /**
     * Get all files in a directory by regex filter if needed.
     * Based on https://stackoverflow.com/a/35105800
     * @param string $path
     * @param bool $recursively
     * @param string $filter
     * @return array
     * @throws \RuntimeException
     */
    public function scanDirectory($path, $recursively = false, $filter = '')
    {
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is not a directory and cannot be scanned', $path));
        }
        $results = [];

        foreach (scandir($path) as $object) {
            $objectPath = $path . '/' . $object;

            if (!is_dir($objectPath)) {
                if (!$filter || preg_match($filter, $objectPath)) {
                    $results[] = $objectPath;
                }
            } elseif ($recursively && $object !== '.' && $object !== '..') {
                $results = array_merge($results, $this->scanDirectory($objectPath, true, $filter));
            }
        }

        return $results;
    }
}
