<?php
declare(strict_types=1);

namespace Awesome\Framework\Model;

class FileManager implements \Awesome\Framework\Model\SingletonInterface
{
    private const DEFAULT_ACCESS_MODE = 0777;

    /**
     * Create file.
     * @param string $path
     * @param string $content
     * @param bool $replace
     * @return bool
     * @throws \RuntimeException
     */
    public function createFile(string $path, string $content = '', bool $replace = false): bool
    {
        if (file_exists($path)) {
            if (!is_file($path)) {
                throw new \RuntimeException(sprintf('Provided path "%s" is not a file and cannot be replaced', $path));
            }
            if (!$replace) {
                throw new \RuntimeException(sprintf('Provided file "%s" already exists and cannot be replaced', $path));
            }
        }
        if (!is_dir(dirname($path))) {
            $this->createDirectory(dirname($path));
        }

        return @file_put_contents($path, $content) !== false;
    }

    /**
     * Create symlink.
     * Fallback to hard link in case symlink is not permitted to be created on Windows.
     * @param string $target
     * @param string $link
     * @param bool $replace
     * @return bool
     * @throws \RuntimeException
     */
    public function createSymlink(string $target, string $link, bool $replace = false): bool
    {
        if (file_exists($link)) {
            if (!$replace) {
                throw new \RuntimeException(sprintf('Provided link path "%s" already exists and cannot be replaced', $link));
            }
            $this->removeFile($link);
        }
        if (!is_dir(dirname($link))) {
            $this->createDirectory(dirname($link));
        }

        return (@symlink($target, $link) ?: @link($target, $link)) !== false;
    }

    /**
     * Read file.
     * By default will throw an exception if file does not exist.
     * @param string $path
     * @param bool $graceful
     * @return string|false
     * @throws \RuntimeException
     */
    public function readFile(string $path, bool $graceful = false)
    {
        if (file_exists($path)) {
            if (!is_file($path)) {
                throw new \RuntimeException(sprintf('Provided path "%s" is not a file and cannot be read', $path));
            }
        } elseif (!$graceful) {
            throw new \RuntimeException(sprintf('Provided path "%s" does not exist', $path));
        }

        return @file_get_contents($path);
    }

    /**
     * Write content to a file, appending by default.
     * File will be created if not exists.
     * @param string $path
     * @param string $content
     * @param bool $append
     * @return bool
     * @throws \RuntimeException
     */
    public function writeToFile(string $path, string $content, bool $append = true): bool
    {
        if (file_exists($path)) {
            if (!is_file($path)) {
                throw new \RuntimeException(sprintf('Provided path "%s" is not a file and cannot be written', $path));
            }
        } else {
            $this->createFile($path);
        }

        return @file_put_contents($path, $content, $append ? FILE_APPEND : 0) !== false;
    }

    /**
     * Remove file.
     * @param string $path
     * @return bool
     * @throws \RuntimeException
     */
    public function removeFile(string $path): bool
    {
        if (file_exists($path) && !is_file($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is not a file and cannot be removed', $path));
        }

        return @unlink($path);
    }

    /**
     * Copy file replacing destination by default.
     * @param string $source
     * @param string $destination
     * @param bool $replace
     * @return bool
     * @throws \RuntimeException
     */
    public function copyFile(string $source, string $destination, $replace = true): bool
    {
        if (!is_file($source)) {
            throw new \RuntimeException(
                sprintf('Provided source path "%s" does not exist or is not a file and cannot be copied', $source)
            );
        }
        if (!is_dir(dirname($destination))) {
            $this->createDirectory(dirname($destination));
        }
        if (file_exists($destination)) {
            if (!$replace) {
                throw new \RuntimeException(sprintf('Provided destination file "%s" already exists', $destination));
            }
            $this->removeFile($destination);
        }

        return copy($source, $destination);
    }

    /**
     * Create directory recursively.
     * @param string $path
     * @param int $mode
     * @return bool
     * @throws \RuntimeException
     */
    public function createDirectory(string $path, int $mode = self::DEFAULT_ACCESS_MODE): bool
    {
        if (file_exists($path) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" already exists and is not a directory', $path));
        }

        return @mkdir($path, $mode, true);
    }

    /**
     * Remove directory recursively.
     * @link https://www.php.net/manual/en/function.rmdir.php#117354
     * @param string $path
     * @return bool
     * @throws \RuntimeException
     */
    public function removeDirectory(string $path): bool
    {
        if (file_exists($path) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is not a directory', $path));
        }
        foreach (@scandir($path) ?: [] as $object) {
            if ($object !== '.' && $object !== '..') {
                if (is_dir($path . '/' . $object)) {
                    $this->removeDirectory($path . '/' . $object);
                } else {
                    unlink($path . '/' . $object);
                }
            }
        }

        return @rmdir($path);
    }

    /**
     * Get all files in a directory by extensions filter if needed.
     * @link https://stackoverflow.com/a/35105800
     * @param string $path
     * @param bool $recursively
     * @param string|array $extensions
     * @return array
     * @throws \RuntimeException
     */
    public function scanDirectory(string $path, bool $recursively = false, $extensions = ''): array
    {
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Provided path "%s" is not a directory and cannot be scanned', $path));
        }
        $results = [];
        $extensions = is_array($extensions) ? $extensions : [$extensions];

        foreach (scandir($path) as $object) {
            $objectPath = $path . '/' . $object;

            if (!is_dir($objectPath)) {
                if (!$extensions || in_array(pathinfo($objectPath, PATHINFO_EXTENSION), $extensions, true)) {
                    $results[] = $objectPath;
                }
            } elseif ($recursively && $object !== '.' && $object !== '..') {
                $results = array_merge($results, $this->scanDirectory($objectPath, true, $extensions));
            }
        }

        return $results;
    }
}
