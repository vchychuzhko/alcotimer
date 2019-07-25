<?php

namespace Awesome\Base\Model;

class XmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const CLI_CACHE_KEY = 'cli';

    /**
     * @var \Awesome\Cache\Model\Cache $cache
     */
    private $cache;

    /**
     * Clean constructor.
     */
    function __construct()
    {
        $this->cache = new \Awesome\Cache\Model\Cache();
    }

    /**
     * Collect data about all available console commands.
     * @return array
     */
    public function retrieveConsoleCommands()
    {
        if (!$commandList = $this->cache->get(self::CLI_CACHE_KEY)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);
                $namespace = (string)$cliData['namespace'];

                if (!isset($commandList[$namespace])) {
                    $commandList[$namespace] = [];
                }

                foreach ($cliData->command as $commandNode) {
                    $options = [];

                    foreach ($commandNode->optionList as $option) {
                        $options[(string)$option->option['name']] = [
                            'required' => $this->stringToBoolean((string)$option->option['required']),
                            'mask' => (string)$option->option->mask,
                            'description' => (string)$option->option->description
                        ];
                    }

                    $commandList[$namespace][(string)$commandNode['name']] = [
                        'class' => (string)$commandNode['class'],
                        'description' => (string)$commandNode->description,
                        'options' => $options
                    ];
                }
            }

            $this->cache->save($commandList, self::CLI_CACHE_KEY);
        }

        return $commandList;
    }

    /**
     * Convert xml true/false value into boolean.
     * @param string $valueInString
     * @return boolean
     */
    private function stringToBoolean($valueInString)
    {
        return $valueInString === 'true';
    }
}
