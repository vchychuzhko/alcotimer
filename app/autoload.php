<?php
/**
 * Function to load object by provided object name.
 * @param string $objectName
 * @throws \Exception
 */
spl_autoload_register(function ($objectName) {
    $objectFile = APP_DIR . '/' . str_replace('\\', '/', ltrim($objectName, '\\')) . '.php';

    if (!file_exists($objectFile)) {
        throw new \Exception(sprintf('File for "%s" object was not found.', $objectName));
    }
    require_once $objectFile;

    if (!class_exists($objectName) && !interface_exists($objectName)) {
        throw new \Exception(sprintf('Object "%s" was not found in "%s"', $objectName, $objectFile));
    }
});
