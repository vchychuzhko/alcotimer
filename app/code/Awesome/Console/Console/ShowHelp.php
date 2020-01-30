<?php

namespace Awesome\Console\Console;

class ShowHelp extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var \Awesome\Console\Model\XmlParser\CliXmlParser $xmlParser
     */
    private $xmlParser;

    /**
     * ShowHelp constructor.
     * @inheritDoc
     */
    public function __construct($options = [], $arguments = [])
    {
        $this->xmlParser = new \Awesome\Console\Model\XmlParser\CliXmlParser();
        parent::__construct($options, $arguments);
    }

    /**
     * Show help with app version and list of all available commands.
     * @inheritDoc
     */
    public function execute($output)
    {
        $output->writeln($output->colourText('Usage:', 'brown'));
        $output->writeln('command [options] [arguments]', 2);
        $output->writeln();

        //@TODO: Implement command options functionality
        $options = [
            [
                'name' => 'help',
                'shortcut' => 'h',
                'mode' => 'optional',
                'description' => 'Display this help message',
                'default' => null
            ],
            [
                'name' => 'quiet',
                'shortcut' => 'q',
                'mode' => 'optional',
                'description' => 'Do not output any message',
                'default' => null
            ],
            [
                'name' => 'version',
                'shortcut' => 'v',
                'mode' => 'optional', //     const VALUE_NONE = 1;    const VALUE_REQUIRED = 2;    const VALUE_OPTIONAL = 4;    const VALUE_IS_ARRAY = 8;
                'description' => 'Display this application version',
                'default' => null
            ],
//            '-n, --no-interaction' => 'Do not ask any interactive questions'
        ];

        $padding = max(array_map(function ($option) {
            //@TODO: Move option pre-render to a separate function
            return strlen('-' . $option['shortcut'] . ', --' . $option['name']);
        }, $options));

        if ($options) {
            $output->writeln($output->colourText('Options:', 'brown'));

            foreach ($options as $option) {
                $output->writeln(
                    str_pad('-' . $option['shortcut'] . ', --' . $option['name'], $padding + 2) . $option['description']
                );
            }
            $output->writeln();
        }

        if ($commandList = $this->xmlParser->retrieveConsoleCommands()) {
            $output->writeln($output->colourText('Available commands:', 'brown'));

            foreach ($commandList as $namespace => $commands) {
                $output->writeln($output->colourText($namespace, 'brown'), 1);

                foreach ($commands as $name => $command) {
                    if (!$command['disabled']) {
                        $output->writeln(
                            str_pad($output->colourText($namespace . ':' . $name), 30) . $command['description'],
                            2
                        );
                    }
                }
            }
        } else {
            $output->writeln('No commands are currently available.');
        }
    }
}
