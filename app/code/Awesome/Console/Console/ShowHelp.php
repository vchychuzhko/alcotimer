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
     */
    public function __construct()
    {
        $this->xmlParser = new \Awesome\Console\Model\XmlParser\CliXmlParser();
    }

    /**
     * Show help with the list of all available commands.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $output = "---- AlcoTimer CLI ----\n";

        if ($commandList = $this->xmlParser->retrieveConsoleCommands()) {
            $output .= 'Here is the list of available commands:';

            foreach ($commandList as $namespace => $commands) {
                foreach ($commands as $name => $command) {
                    $optionList = $command['optionList'] ?? [];
                    $output .= "\n" . $this->colourText(
                        \Awesome\Console\Model\Console::COMMAND_BASE . ' ' . $namespace . ':' . $name
                        ) . ' | ' . $command['description'];

                    foreach ($optionList as $option) {
                        $required = $option['required'] ? '' : ' (optional)';

                        $output .= "\n" . $option['mask'] . ' - '
                            . ($option['description'] ?? '')
                            . $required;
                    }
                }
            }
        } else {
            $output .= 'No commands are currently available.';
        }

        return $output;
    }
}
