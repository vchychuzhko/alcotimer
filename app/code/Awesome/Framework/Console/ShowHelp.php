<?php

namespace Awesome\Framework\Console;

use Awesome\Framework\XmlParser\CliXmlParser;

class ShowHelp extends \Awesome\Framework\Model\Cli\AbstractCommand
{
    /**
     * @var CliXmlParser $xmlParser
     */
    private $xmlParser;

    /**
     * ShowHelp constructor.
     */
    public function __construct()
    {
        $this->xmlParser = new CliXmlParser();
    }

    /**
     * Show help with app version and list of all available commands.
     * @inheritDoc
     */
    public function execute($input, $output)
    {
        //@TODO: this must not be a console command, move it to Cli, perhaps
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

        $arguments = [
            [
                'name' => 'cache-types',
                'mode' => 'optional', //     const REQUIRED = 1;    const OPTIONAL = 2;    const IS_ARRAY = 4;
                'description' => 'Cache types to be cleared',
                'default' => null
            ]
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

        if ($commandList = $this->xmlParser->getConsoleCommands()) {
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
