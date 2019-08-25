<?php

namespace Awesome\Console\Console;

class ShowHelp extends \Awesome\Console\Model\AbstractCommand
{
    /**
     * @var \Awesome\Base\Model\XmlParser $xmlParser
     */
    private $xmlParser;

    /**
     * ShowHelp constructor.
     */
    public function __construct()
    {
        $this->xmlParser = new \Awesome\Base\Model\XmlParser();
    }

    /**
     * Show help with the list of all available commands.
     * @inheritDoc
     */
    public function execute($args = [])
    {
        $output = "---- AlcoTimer CLI ----\n";

        if ($commandList = $this->xmlParser->retrieveConsoleCommands()) {
            //@TODO: update reading functionality
            $output .= 'Here is the list of available commands:';

            foreach ($commandList as $namespace => $commands) {
                foreach ($commands as $name => $command) {
                    $optionList = $command['optionList'] ?? [];
                    $output .= "\n" . $this->colourText(
                        \Awesome\Console\Model\Console::COMMAND_BASE . ' ' . $namespace . ':' . $name
                        ) . ' | ' . $command['description']['text'];

                    foreach ($optionList as $option) {
                        $output .= "\n" . $option['mask']['text'] . ' - '
                            . $option['description']['text'] ?? ''
                            . ($option['required'] ? '' : ' (optional)');
                    }
                }
            }
        } else {
            $output .= 'No commands are currently available.';
        }

        return $output;
    }
}
