<?php

namespace Awesome\Console\Model\XmlParser;

class CliXmlParser extends \Awesome\Base\Model\XmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const ETC_CACHE_KEY = 'etc';
    private const CLI_CACHE_TAG = 'cli';

    /**
     * Collect data about all available console commands.
     * @return array
     */
    public function retrieveConsoleCommands()
    {
        if (!$commandList = $this->cache->get(self::ETC_CACHE_KEY, self::CLI_CACHE_TAG)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);

                $parsedData = $this->parseXmlNode($cliData);
                $commandList = array_merge_recursive($commandList, $parsedData['config']['children']);
            }

            $this->cache->save(self::ETC_CACHE_KEY, self::CLI_CACHE_TAG, $commandList);
        }

        return $commandList;
    }
}
