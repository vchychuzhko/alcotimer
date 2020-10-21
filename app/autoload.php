<?php
declare(strict_types=1);

/**
 * Function to load object file by provided name.
 * @param string $objectName
 * @throws \Exception
 */
spl_autoload_register(static function (string $objectName) {
    $objectFile = APP_DIR . '/' . str_replace('\\', '/', ltrim($objectName, '\\')) . '.php';

    if (!file_exists($objectFile)) {
        throw new \Exception(sprintf('Object file was not found for "%s" object', $objectName));
    }
    if (is_dir($objectFile)) {
        throw new \Exception(sprintf('Object path "%s" is a directory and cannot be loaded', $objectName));
    }
    require_once $objectFile;

    if (!class_exists($objectName) && !interface_exists($objectName)) {
        throw new \Exception(sprintf('Object "%s" was not found in "%s"', $objectName, $objectFile));
    }
});
