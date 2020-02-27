<?php

namespace Awesome\Framework\XmlParser;

use Awesome\Cache\Model\Cache;
use Awesome\Framework\Model\Cli\AbstractCommand;
use Awesome\Framework\Model\Cli\Input\InputDefinition;

class CliXmlParser extends \Awesome\Framework\Model\XmlParser\AbstractXmlParser
{
    private const CLI_XML_PATH_PATTERN = '/*/*/etc/cli.xml';
    private const CLI_HANDLES_CACHE_TAG = 'cli-handles';
    private const DEFAULT_HANDLE = 'default';

    /**
     * Return data for AbstractCommand in case there is no requested command.
     * @inheritDoc
     */
    public function get($handle)
    {
        if ($handle === '') {
            $handle = self::DEFAULT_HANDLE;
        }

        if (!$commandData = $this->cache->get(Cache::CLI_CACHE_KEY, $handle)) {
            $commandList = $this->getHandlesClasses();
            $definition = new InputDefinition();

            if (isset($commandList[$handle])) {
                /** @var AbstractCommand $commandClass */
                $commandClass = $commandList[$handle];
                $definition = $commandClass::configure($definition);

                $commandData = array_replace_recursive(['class' => $commandClass], $definition->getDefinition());
            } elseif ($handle === self::DEFAULT_HANDLE) {
                $definition = AbstractCommand::configure($definition);

                $commandData = $definition->getDefinition();
            }

            $this->cache->save(Cache::CLI_CACHE_KEY, $handle, $commandData);
        }

        return $commandData;
    }

    /**
     * @inheritDoc
     */
    public function getHandles()
    {
        return array_keys($this->getHandlesClasses());
    }

    /**
     * Get all available handles with their responsible classes.
     * If includeDisabled is true, return also disabled commands.
     * @param bool $includeDisabled
     * @return array
     */
    public function getHandlesClasses($includeDisabled = false)
    {
        if (!$handles = $this->cache->get(Cache::CLI_CACHE_KEY, self::CLI_HANDLES_CACHE_TAG)) {
            foreach (glob(APP_DIR . self::CLI_XML_PATH_PATTERN) as $cliXmlFile) {
                $cliData = simplexml_load_file($cliXmlFile);
                $parsedData = $this->parse($cliData);

                foreach ($parsedData as $commandName => $command) {
                    if (!$command['disabled'] || $includeDisabled) {
                        if (isset($handles[$commandName])) {
                            throw new \LogicException(sprintf('Command "%s" is already defined.', $commandName));
                        }
                        $handles[$commandName] = $command['class'];
                    }
                }
            }

            $this->cache->save(Cache::CLI_CACHE_KEY, self::CLI_HANDLES_CACHE_TAG, $handles);
        }

        return $handles;
    }

    /**
     * @inheritDoc
     * @throws \LogicException
     */
    protected function parse($node)
    {
        $parsedNode = [];

        foreach ($node->children() as $namespace) {
            foreach ($namespace->children() as $command) {
                $commandName = $this->getNodeAttribute($namespace) . ':' . $this->getNodeAttribute($command);

                if (isset($parsedNode[$commandName])) {
                    throw new \LogicException(sprintf('Command "%s" is defined twice in one file.', $commandName));
                }
                $class = '\\' . ltrim($this->getNodeAttribute($command, 'class'), '\\');

                if (!$class) {
                    throw new \LogicException(sprintf('Class is not specified for "%s" command.', $commandName));
                }
                $disabled = $this->stringBooleanCheck($this->getNodeAttribute($command, 'disabled'));

                $parsedNode[$commandName] = [
                    'class' => $class,
                    'disabled' => $disabled
                ];
            }
        }

        return $parsedNode;
    }
}
